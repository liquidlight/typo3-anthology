<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Service\FlexFormService;

abstract class AbstractConfigurationHook
{
	public const ALL_TYPES = '*';

	protected const CONTENT_TABLE = 'tt_content';

	public function __construct(
		protected readonly FilterFactory $filterFactory,
		protected readonly RepositoryFactory $repositoryFactory,
		protected readonly FlexFormService $flexFormService,
		protected readonly ConnectionPool $connectionPool
	) {
	}

	public function getFields(array &$params, array $allowedTypes, ?int $anthologyPluginUid = null): void
	{
		global $TCA;

		$anthologyPluginUid = $anthologyPluginUid ?? $this->getAnthologyPluginUid($params);

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

		$allowAllTypes = count($allowedTypes) === 1 && reset($allowedTypes) === static::ALL_TYPES;

		$eligibleColumns = array_filter(
			$TCA[$anthologyPluginTca]['columns'],
			static fn ($column) => in_array($column['config']['type'], $allowedTypes) || $allowAllTypes
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

	protected function getAnthologyPluginUid(array $params): int
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
		$queryBuilder = $this->connectionPool->getQueryBuilderForTable(static::CONTENT_TABLE);
		$queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);

		$queryBuilder
			->select('uid')
			->from(static::CONTENT_TABLE)
			->where(
				/**
				 * Like I said, this __really__ isn't ideal, especially this `LIKE`.
				 * We need to test whether this breaks if there is more than one
				 * plugin on the page, and also ensure that any Anthology extensions
				 * use a compatible naming format for the plugin or this will not
				 * display any available filter fields
				 */
				$queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('llanthology%_%view')),
				$queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($params['effectivePid'], Connection::PARAM_INT)),
				"FIND_IN_SET(" . $queryBuilder->createNamedParameter($params['row']['uid'], Connection::PARAM_INT) . ", EXTRACTVALUE(`pi_flexform`, '//field[@index=\'settings.filters\']/value'))"
			)
		;

		return (int)$queryBuilder->executeQuery()->fetchOne();
	}

	protected function getAnthologyPluginTca(int $anthologyPluginUid): ?string
	{
		$anthologyPluginConfiguration = $this->getAnthologyPluginConfiguration($anthologyPluginUid);

		if (!$anthologyPluginConfiguration) {
			return null;
		}

		return $this->repositoryFactory->getTcaName($anthologyPluginConfiguration['settings']['repository']);
	}

	protected function getAnthologyPluginConfiguration(int $anthologyPluginUid): array
	{
		$queryBuilder = $this->connectionPool->getQueryBuilderForTable(static::CONTENT_TABLE);

		$queryBuilder
			->select('pi_flexform')
			->from(static::CONTENT_TABLE)
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
