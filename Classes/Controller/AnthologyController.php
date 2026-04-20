<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Controller;

use LiquidLight\Anthology\Domain\Repository\FilterRepository;
use LiquidLight\Anthology\Events\BeforeAnthologyListViewRenderEvent;
use LiquidLight\Anthology\Events\BeforeAnthologySingleViewRenderEvent;
use LiquidLight\Anthology\Events\BeforeGetAllRecordsEvent;
use LiquidLight\Anthology\Events\BeforeGetRecordsWithConstraintsEvent;
use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use LiquidLight\Anthology\Provider\PageTitleProvider;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

class AnthologyController extends ActionController
{
	protected const LIST_MODE = 'list';

	protected const SINGLE_MODE = 'single';

	protected const DEFAULT_PER_PAGE = 10;

	protected const DEFAULT_MAXIMUM_LINKS = 8;

	protected Repository $repository;

	public function __construct(
		protected RepositoryFactory $repositoryFactory,
		protected FilterFactory $filterFactory,
		protected FilterRepository $filterRepository,
		protected PageTitleProvider $pageTitleProvider,
		protected PackageManager $packageManager,
		protected Registry $registry
	) {
	}

	public function viewAction(): ResponseInterface
	{
		switch ($this->settings['mode']) {
			case static::LIST_MODE:
				return new ForwardResponse(static::LIST_MODE);

			case static::SINGLE_MODE:
				return new ForwardResponse(static::SINGLE_MODE);

			default:
				throw new ImmediateResponseException(
					$this->pageNotFoundAction(
						sprintf(
							'Invalid mode "%s" selected',
							$this->settings['mode']
						),
					),
					1759164121
				);
		}
	}

	public function listAction(): ResponseInterface
	{
		$this->addTemplatePaths();

		$this->view->assign('configuration', $this->request->getAttribute('site')->getConfiguration());

		$this->view->assign('filters', $this->getFilters());
		$this->view->assignMultiple($this->getPaginatedItems());

		$this->eventDispatcher->dispatch(
			new BeforeAnthologyListViewRenderEvent(
				$this->view,
				$this->request
			)
		);

		return $this->htmlResponse();
	}

	public function singleAction(): ResponseInterface
	{
		$this->addTemplatePaths();

		$this->view->assign('configuration', $this->request->getAttribute('site')->getConfiguration());

		$recordUid = $this->request->hasArgument('record')
			? $this->request->getArgument('record')
			: 0;

		if (!$recordUid) {
			throw new ImmediateResponseException(
				$this->pageNotFoundAction(
					'Invalid or no record UID supplied',
				),
				1759233850
			);
		}

		$record = $this->getRepository()->findByUid((int)$recordUid);

		if (!$record) {
			throw new ImmediateResponseException(
				$this->pageNotFoundAction(
					sprintf(
						'Record with UID %d not found',
						$recordUid
					)
				),
				1759233928
			);
		}

		$this->pageTitleProvider->setTitle($record, $this->repositoryFactory->getTcaName($this->getRepository()));
		$this->registry->set('ll_anthology', 'record_page_title', $this->pageTitleProvider->getTitle());

		$this->view->assign('record', $record);

		$this->eventDispatcher->dispatch(
			new BeforeAnthologySingleViewRenderEvent(
				$record,
				$this->view,
				$this->request
			)
		);

		return $this->htmlResponse();
	}

	protected function pageNotFoundAction(string $reason = ''): ResponseInterface
	{
		return GeneralUtility::makeInstance(ErrorController::class)
			->pageNotFoundAction(
				$this->request,
				$reason,
				[
					'code' => PageAccessFailureReasons::PAGE_NOT_FOUND,
				]
			)
		;
	}

	protected function addTemplatePaths(): void
	{
		$repositoryPackageKey = $this->getRepositoryPackageKey();

		if (!$repositoryPackageKey) {
			return;
		}

		$repositoryExtensionPath = ExtensionManagementUtility::extPath($repositoryPackageKey);

		$renderingContext = $this->view->getRenderingContext();
		$templatePaths = $renderingContext->getTemplatePaths();

		$layoutRootPaths = $templatePaths->getLayoutRootPaths();
		$templateRootPaths = $templatePaths->getTemplateRootPaths();
		$partialRootPaths = $templatePaths->getPartialRootPaths();

		$layoutRootPaths[] = $repositoryExtensionPath . 'Resources/Private/Layouts/';
		$templateRootPaths[] = $repositoryExtensionPath . 'Resources/Private/Templates/';
		$partialRootPaths[] = $repositoryExtensionPath . 'Resources/Private/Partials/';

		$layoutRootPaths = array_merge($layoutRootPaths, $this->settings['view']['layoutRootPaths'] ?? []);
		$templateRootPaths = array_merge($templateRootPaths, $this->settings['view']['templateRootPaths'] ?? []);
		$partialRootPaths = array_merge($partialRootPaths, $this->settings['view']['partialRootPaths'] ?? []);

		$templatePaths->setLayoutRootPaths($layoutRootPaths);
		$templatePaths->setTemplateRootPaths($templateRootPaths);
		$templatePaths->setPartialRootPaths($partialRootPaths);

		if (!empty($this->settings['template'])) {
			$this->view->getRenderingContext()->setControllerAction($this->settings['template']);
		}
	}

	protected function getRepositoryPackageKey(): ?string
	{
		foreach ($this->packageManager->getActivePackages() as $package) {
			foreach (array_keys((array)($package->getValueFromComposerManifest()?->autoload?->{'psr-4'})) as $namespace) {
				if (strpos($this->getRepository()::class, $namespace) === 0) {
					return $package->getPackageKey();
				}
			}
		}

		return null;
	}

	protected function getPaginatedItems(): array
	{
		$currentPage = $this->request->hasArgument('currentPage')
			? (int)$this->request->getArgument('currentPage')
			: 1;

		$records = $this->getRecords();

		$paginator = new QueryResultPaginator(
			$records,
			$currentPage,
			(int)(
				$this->settings['itemsPerPage'] ?? false
				? $this->settings['itemsPerPage']
				: static::DEFAULT_PER_PAGE
			)
		);

		$pagination = new SlidingWindowPagination(
			$paginator,
			(int)(
				$this->settings['maximumLinks'] ?? false
				? $this->settings['maximumLinks']
				: static::DEFAULT_MAXIMUM_LINKS
			)
		);

		return [
			'currentPage' => $currentPage,
			'pagination' => $pagination,
			'paginator' => $paginator,
		];
	}

	protected function getRecords(): QueryResult
	{
		$repository = $this->getRepository();
		$filters = $this->getFilters(true);

		if (count($filters) === 0) {
			$this->eventDispatcher->dispatch(
				new BeforeGetAllRecordsEvent(
					$repository,
					$this->view,
					$this->request
				)
			);

			return $repository->findAll();
		}

		$query = $repository->createQuery();

		$constraints = $this->filterFactory->getConstraints(
			$filters,
			$query
		);

		$constraintModeMethod = match ($this->settings['filterMode']) {
			'and' => 'logicalAnd',
			'or' => 'logicalOr',
			default => throw new RuntimeException(
				'Invalid filter mode selected',
				1761738397
			),
		};

		$this->eventDispatcher->dispatch(
			new BeforeGetRecordsWithConstraintsEvent(
				$repository,
				$query,
				$constraints,
				$constraintModeMethod,
				$this->view,
				$this->request
			)
		);

		return $query
			->matching(
				$query->{$constraintModeMethod}(
					...$constraints
				)
			)
			->execute()
		;
	}

	protected function getRepository(): Repository
	{
		if (isset($this->repository)) {
			return $this->repository;
		}

		$this->repository = $this->repositoryFactory->getRepository(
			$this->settings['repository']
		);

		return $this->repository;
	}

	protected function getFilters(bool $ignoreUnsetFilters = false): QueryResult
	{
		$filterQuerySettings = $this->filterRepository->createQuery()->getQuerySettings();
		$filterQuerySettings->setRespectStoragePage(false);
		$this->filterRepository->setDefaultQuerySettings($filterQuerySettings);

		$activeFilters = $this->getActiveFilters();
		$filterUids = GeneralUtility::intExplode(',', $this->settings['filters'], true);

		$filters = $this->filterRepository->findByUids(
			$ignoreUnsetFilters
				? array_intersect($filterUids, array_keys($activeFilters ?? []))
				: $filterUids
		);

		$this->settings['recordStorageUids'] = $filterQuerySettings->getStoragePageIds();
		$this->settings['tca'] = $this->repositoryFactory->getTcaName($this->settings['repository']);

		foreach ($filters as $filter) {
			$filter->setOptions($this->filterFactory->getFilters()[$filter->filterType]::getOptions($filter, $this->settings));
			$filter->setParameter($activeFilters[$filter->getUid()] ?? null);
		}

		return $filters;
	}

	protected function getActiveFilters(): ?array
	{
		$activeFilters = $this->request->hasArgument('filter')
			? array_filter($this->request->getArgument('filter'))
			: [];

		unset($activeFilters['init']);

		return count($activeFilters) ? $activeFilters : null;
	}
}
