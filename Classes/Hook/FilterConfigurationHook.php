<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Service\FlexFormService;

class FilterConfigurationHook
{
	private const CONTENT_TABLE = 'tt_content';

	private const SEARCH_FIELD_TYPES = [
		'input',
		'text',
	];

	private const DATE_FIELD_TYPES = [
		'datetime',
	];

	public function __construct(
		private FilterFactory $filterFactory,
		private RepositoryFactory $repositoryFactory,
		private FlexFormService $flexFormService,
		private ConnectionPool $connectionPool
	) {
	}

	public function getAvailableFilters(array &$params): void
	{
		$filters = $this->filterFactory->getFilters();

		$filterItems = array_map(
			fn ($filterType, $filterClass) => [
				'label' => $filterClass::getLabel(),
				'value' => $filterType,
			],
			array_keys($filters),
			$filters
		);

		usort($filterItems, fn ($a, $b) => $a['label'] <=> $b['label']);

		$params['items'] = [
			[
				'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.please_select',
				'value' => 0,
			],
			...$filterItems,
		];
	}

	public function getSearchFields(array &$params): void
	{
		$this->getFields($params, self::SEARCH_FIELD_TYPES);
	}

	public function getDateFields(array &$params): void
	{
		$this->getFields($params, self::DATE_FIELD_TYPES);
	}

	public function getFields(array &$params, array $allowedTypes): void
	{
		global $TCA;

		$anthologyPluginUid = $this->getAnthologyPluginUid($params);

		if (!$anthologyPluginUid) {
			return;
		}

		$anthologyPluginTca = $this->getAnthologyPluginTca($anthologyPluginUid);

		if (
			!$anthologyPluginTca
			|| !($TCA[$anthologyPluginTca] ?? false)
		) {
			return;
		}

		$eligibleColumns = array_filter(
			$TCA[$anthologyPluginTca]['columns'],
			fn ($column) => in_array($column['config']['type'], $allowedTypes)
		);

		$params['items'] = array_map(
			fn ($column, $columnKey) => [
				'label' => $column['label'] ?? $columnKey,
				'value' => $columnKey,
			],
			$eligibleColumns,
			array_keys($eligibleColumns)
		);
	}

	private function getAnthologyPluginUid(array $params): int
	{
		if ((int)$params['inlineParentUid'] ?? false) {
			return (int)$params['inlineParentUid'];
		}

		if ((int)$params['inlineTopMostParentUid'] ?? false) {
			return (int)$params['inlineTopMostParentUid'];
		}

		/**
		 * This isn't an ideal way to get the UID, but in the absence of either
		 * of the above values, it's the best option available
		 */
		$queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::CONTENT_TABLE);
		$queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);

		$queryBuilder
			->select('uid')
			->from(self::CONTENT_TABLE)
			->where(
				$queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('llanthology_anthologyview')),
				$queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($params['effectivePid'], Connection::PARAM_INT)),
				"FIND_IN_SET(" . $queryBuilder->createNamedParameter($params['row']['uid'], Connection::PARAM_INT) . ", EXTRACTVALUE(`pi_flexform`, '//field[@index=\'settings.filters\']/value'))"
			)
		;

		return (int)$queryBuilder->executeQuery()->fetchOne();
	}

	private function getAnthologyPluginTca(int $anthologyPluginUid): ?string
	{
		$anthologyPluginConfiguration = $this->getAnthologyPluginConfiguration($anthologyPluginUid);

		if (!$anthologyPluginConfiguration) {
			return null;
		}

		return $this->repositoryFactory->getTcaName($anthologyPluginConfiguration['settings']['repository']);
	}

	private function getAnthologyPluginConfiguration(int $anthologyPluginUid): array
	{
		$queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::CONTENT_TABLE);

		$queryBuilder
			->select('pi_flexform')
			->from(self::CONTENT_TABLE)
			->where(
				$queryBuilder->expr()->eq(
					'uid',
					$queryBuilder->createNamedParameter(
						$anthologyPluginUid,
						Connection::PARAM_INT
					)
				)
			)
			->setMaxResults(1)
		;

		return $this->flexFormService->convertFlexFormContentToArray(
			$queryBuilder->executeQuery()->fetchOne()
		);
	}
}
