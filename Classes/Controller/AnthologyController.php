<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Controller;

use LiquidLight\Anthology\Domain\Repository\FilterRepository;
use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use LiquidLight\Anthology\Provider\PageTitleProvider;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
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

class AnthologyController extends ActionController
{
	private const LIST_MODE = 'list';

	private const SINGLE_MODE = 'single';

	private const DEFAULT_PER_PAGE = 10;

	private const DEFAULT_MAXIMUM_LINKS = 8;

	public function __construct(
		private RepositoryFactory $repositoryFactory,
		private FilterFactory $filterFactory,
		private FilterRepository $filterRepository,
		private PageTitleProvider $pageTitleProvider,
		private PackageManager $packageManager,
		private Registry $registry
	) {
	}

	public function viewAction(): ResponseInterface
	{
		switch ($this->settings['mode']) {
			case self::LIST_MODE:
				return new ForwardResponse(self::LIST_MODE);

			case self::SINGLE_MODE:
				return new ForwardResponse(self::SINGLE_MODE);

			default:
				throw new RuntimeException(
					sprintf(
						'Invalid mode "%s" selected',
						$this->settings['mode']
					),
					1759164121
				);
		}
	}

	public function listAction(): ResponseInterface
	{
		$this->addTemplatePaths();

		$this->view->assign('filters', $this->getFilters());
		$this->view->assignMultiple($this->getPaginatedItems());

		return $this->htmlResponse();
	}

	public function singleAction(): ResponseInterface
	{
		$this->addTemplatePaths();

		$recordUid = $this->request->hasArgument('record')
			? $this->request->getArgument('record')
			: 0;

		if (!$recordUid) {
			throw new RuntimeException(
				'Invalid or no record UID supplied',
				1759233850
			);
		}

		$repository = $this->getRepository();
		$record = $repository->findByUid((int)$recordUid);

		if (!$record) {
			throw new PageNotFoundException(
				sprintf(
					'Record with UID %d not found',
					$recordUid
				),
				1759233928
			);
		}

		$this->pageTitleProvider->setTitle($record, $this->settings['tca']);
		$this->registry->set('ll_anthology', 'record_page_title', $this->pageTitleProvider->getTitle());

		$this->view->assign('record', $record);

		return $this->htmlResponse();
	}

	private function addTemplatePaths(): void
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

	private function getRepositoryPackageKey(): ?string
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

	private function getPaginatedItems(): array
	{
		$repository = $this->getRepository();

		$currentPage = $this->request->hasArgument('currentPage')
			? (int)$this->request->getArgument('currentPage')
			: 1;

		$records = $this->getRecords($repository);

		$paginator = new QueryResultPaginator(
			$records,
			$currentPage,
			(int)(
				$this->settings['itemsPerPage'] ?? false
				? $this->settings['itemsPerPage']
				: self::DEFAULT_PER_PAGE
			)
		);

		$pagination = new SlidingWindowPagination(
			$paginator,
			(int)(
				$this->settings['maximumLinks'] ?? false
				? $this->settings['maximumLinks']
				: self::DEFAULT_MAXIMUM_LINKS
			)
		);

		return [
			'currentPage' => $currentPage,
			'pagination' => $pagination,
			'paginator' => $paginator,
		];
	}

	private function getRecords(Repository $repository): QueryResult
	{
		$filters = $this->getFilters(true);

		if (count($filters) === 0) {
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

		return $query
			->matching(
				$query->{$constraintModeMethod}(
					...$constraints
				)
			)
			->execute()
		;
	}

	private function getRepository(): Repository
	{
		$repositoryClasses = $this->repositoryFactory->getRepositories();
		$repositoryClass = $repositoryClasses[$this->settings['tca']] ?? null;

		if (!$repositoryClass) {
			throw new RuntimeException(
				sprintf(
					'"%s" is not a valid repository selection',
					$this->settings['tca']
				),
				1758814029
			);
		}

		return $this->repositoryFactory->getRepository($repositoryClass);
	}

	private function getFilters(bool $ignoreUnsetFilters = false): QueryResult
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

		foreach ($filters as $filter) {
			$filter->setOptions($this->filterFactory->getFilters()[$filter->filterType]::getOptions($filter, $this->settings));
			$filter->setParameter($activeFilters[$filter->getUid()] ?? null);
		}

		return $filters;
	}

	private function getActiveFilters(): ?array
	{
		$activeFilters = $this->request->hasArgument('filter')
			? array_filter($this->request->getArgument('filter'))
			: [];

		unset($activeFilters['init']);

		return count($activeFilters) ? $activeFilters : null;
	}
}
