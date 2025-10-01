<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
		private FlexFormService $flexFormService,
		private ConnectionPool $connectionPool
	) {
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

		$anthologyPluginUid = (int)$params['inlineParentUid'] ?? 0;

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

	private function getAnthologyPluginTca(int $anthologyPluginUid): ?string
	{
		$anthologyPluginConfiguration = $this->getAnthologyPluginConfiguration($anthologyPluginUid);

		if (!$anthologyPluginConfiguration) {
			return null;
		}

		return $anthologyPluginConfiguration['settings']['tca'] ?? null;
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
