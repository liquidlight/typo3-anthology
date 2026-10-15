<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use LiquidLight\Anthology\Hook\AbstractConfigurationHook;

class PluginConfigurationHook extends AbstractConfigurationHook
{
	public function getRepositories(array &$params): void
	{
		$repositories = $this->repositoryFactory->getRepositories();

		$tcaConfigurations = array_filter(
			$GLOBALS['TCA'],
			fn ($tcaTableName) => in_array(
				$tcaTableName,
				$repositories
			),
			ARRAY_FILTER_USE_KEY
		);

		$repositories = array_intersect($repositories, array_keys($tcaConfigurations));

		$params['items'] = array_map(
			fn ($tableName, $repository) => [
				'value' => $repository,
				'label' => $this->getRepositoryLabel($repository, $tcaConfigurations[$tableName]),
				'icon' => $tcaConfigurations[$tableName]['ctrl']['iconfile'] ?? '',
			],
			$repositories,
			array_keys($repositories),
		);
	}

	public function getSortFields(array &$params): void
	{
		$rowUid = ((int)$params['row']['uid'] ?: 0) ?: null;

		$this->getFields($params, [static::ALL_TYPES], $rowUid);

		$params['items'] = [
			[
				'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_be.xlf:source.sortBy.default',
				'value' => null,
			],
			...$params['items'],
		];
	}

	private function getRepositoryLabel(
		string $repository,
		array $tcaConfiguration
	): string {
		return $this->repositoryFactory->getRepositoryLabel($repository)
			?? $tcaConfiguration['ctrl']['title'];
	}
}
