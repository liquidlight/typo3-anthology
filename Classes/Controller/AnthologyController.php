<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Controller;

use LiquidLight\Anthology\Domain\Repository\FilterRepository;
use LiquidLight\Anthology\Events\BeforeAnthologyListViewRenderEvent;
use LiquidLight\Anthology\Events\BeforeAnthologySingleViewRenderEvent;
use LiquidLight\Anthology\Events\BeforeGetRecordsEvent;
use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use LiquidLight\Anthology\Provider\PageTitleProvider;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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

	protected int $recordUid;

	protected AbstractEntity $record;

	public function __construct(
		protected RepositoryFactory $repositoryFactory,
		protected FilterFactory $filterFactory,
		protected FilterRepository $filterRepository,
		protected PageTitleProvider $pageTitleProvider,
		protected PackageManager $packageManager
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
		$this->assignDefaults();

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
		$this->assignDefaults();

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

		$this->recordUid = (int)$recordUid;

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

		$this->record = $record;

		$this->pageTitleProvider->setTitle($record, $this->repositoryFactory->getTcaName($this->getRepository()));

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

	// Shared variables
	protected function assignDefaults(): void
	{
		$context = Environment::getContext();
		$environment = match (true) {
			$context->isDevelopment() => 'Development',
			$context->isProduction() => 'Production',
			default => (string)$context,
		};

		$this->view->assignMultiple([
			'environment' => $environment,
			'configuration' => $this->request->getAttribute('site')->getConfiguration(),
			'pageUid' => (string)$this->request->getAttribute('routing')?->getPageId(),
			'modelName' => $this->getModelName(),
		]);
	}

	protected function addTemplatePaths(): void
	{
		$repositoryPackageKey = $this->getRepositoryPackageKey();

		if (!$repositoryPackageKey) {
			return;
		}

		$repositoryExtensionPath = ExtensionManagementUtility::extPath($repositoryPackageKey);

		$modelName = $this->getModelName();

		$renderingContext = $this->view->getRenderingContext();
		$templatePaths = $renderingContext->getTemplatePaths();

		// Get current paths
		$layoutRootPaths = $templatePaths->getLayoutRootPaths();
		$templateRootPaths = $templatePaths->getTemplateRootPaths();
		$partialRootPaths = $templatePaths->getPartialRootPaths();

		// Compile paths in current extension
		$layoutRootPaths[] = $repositoryExtensionPath . 'Resources/Private/Layouts/';
		$templateRootPaths[] = $repositoryExtensionPath . 'Resources/Private/Templates/';
		$partialRootPaths[] = $repositoryExtensionPath . 'Resources/Private/Partials/';

		// Compile global Anthology paths configured in Typoscript
		$layoutRootPaths = array_merge($layoutRootPaths, $this->settings['view']['layoutRootPaths'] ?? []);
		$templateRootPaths = array_merge($templateRootPaths, $this->settings['view']['templateRootPaths'] ?? []);
		$partialRootPaths = array_merge($partialRootPaths, $this->settings['view']['partialRootPaths'] ?? []);

		// Compile model specific paths configured in Typoscript
		$layoutRootPaths = array_merge($layoutRootPaths, $this->settings['view']['layoutRootPaths'][strtolower($modelName)] ?? []);
		$templateRootPaths = array_merge($templateRootPaths, $this->settings['view']['templateRootPaths'][strtolower($modelName)] ?? []);
		$partialRootPaths = array_merge($partialRootPaths, $this->settings['view']['partialRootPaths'][strtolower($modelName)] ?? []);

		// Set compiled paths
		$templatePaths->setLayoutRootPaths($layoutRootPaths);
		$templatePaths->setTemplateRootPaths($templateRootPaths);
		$templatePaths->setPartialRootPaths($partialRootPaths);

		// If the template has been set in the plugin settings, add it here
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

	protected function getModelName(): ?string
	{
		$modelClass = $this->repository->createQuery()->getType();
		$modelReflection = new ReflectionClass($modelClass);

		return $modelReflection?->getShortName();
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
		$preFilters = $this->getPreFilters();

		$query = $repository->createQuery();

		if (!empty($this->settings['sortBy'])) {
			$query->setOrderings([
				$this->settings['sortBy'] => !empty($this->settings['sortByDirection']) ? $this->settings['sortByDirection'] : QueryInterface::ORDER_ASCENDING,
			]);
		}

		$constraints = $this->filterFactory->getConstraints($filters, $query);
		$preFilterConstraints = $this->filterFactory->getConstraints($preFilters, $query);

		$constraintModeMethod = $this->getConstraintModeMethod($this->settings['filterMode']);
		$preFilterConstraintModeMethod = $this->getConstraintModeMethod($this->settings['preFilterMode'] ?? 'and');

		$this->eventDispatcher->dispatch(
			new BeforeGetRecordsEvent(
				$repository,
				$query,
				$constraints,
				$constraintModeMethod,
				$preFilterConstraints,
				$preFilterConstraintModeMethod,
				$this->view,
				$this->request,
				$this->settings
			)
		);

		/**
		 * Each group is combined by its own mode, then ANDed together, so
		 * pre-filters always constrain the result regardless of `filterMode`.
		 * An empty group is treated as "no constraint" rather than calling
		 * `logicalOr()` with zero arguments, which TYPO3 resolves to an
		 * always-false constraint (unlike `logicalAnd()`, which is always-true).
		 */
		$topLevelConstraints = array_filter([
			$this->combineConstraints($query, $constraints, $constraintModeMethod),
			$this->combineConstraints($query, $preFilterConstraints, $preFilterConstraintModeMethod),
		]);

		return $query
			->matching(
				empty($topLevelConstraints)
					? $query->logicalAnd()
					: $query->logicalAnd(...$topLevelConstraints)
			)
			->execute()
		;
	}

	protected function getConstraintModeMethod(string $mode): string
	{
		return match ($mode) {
			'and' => 'logicalAnd',
			'or' => 'logicalOr',
			default => throw new RuntimeException(
				'Invalid filter mode selected',
				1761738397
			),
		};
	}

	protected function combineConstraints(
		QueryInterface $query,
		array $constraints,
		string $constraintModeMethod
	): ?ConstraintInterface {
		if (empty($constraints)) {
			return null;
		}

		return $query->{$constraintModeMethod}(...$constraints);
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
			// @extensionScannerIgnoreLine
			$filter->setOptions($this->filterFactory->getFilters()[$filter->filterType]::getOptions($filter, $this->settings));
			$filter->setParameter($activeFilters[$filter->getUid()] ?? null);
		}

		return $filters;
	}

	protected function getPreFilters(): QueryResult
	{
		$filterQuerySettings = $this->filterRepository->createQuery()->getQuerySettings();
		$filterQuerySettings->setRespectStoragePage(false);
		$this->filterRepository->setDefaultQuerySettings($filterQuerySettings);

		$filterUids = GeneralUtility::intExplode(',', $this->settings['preFilters'] ?? '', true);

		$filters = $this->filterRepository->findByUids($filterUids);

		foreach ($filters as $filter) {
			$filter->setParameter($filter->getParsedSettings()['preFilterValue'] ?? null);
		}

		return $filters;
	}

	protected function getActiveFilters(): ?array
	{
		$activeFilters = $this->request->hasArgument('filter')
			? array_filter($this->request->getArgument('filter'))
			: [];

		// @extensionScannerIgnoreLine
		unset($activeFilters['init']);

		return count($activeFilters) ? $activeFilters : null;
	}
}
