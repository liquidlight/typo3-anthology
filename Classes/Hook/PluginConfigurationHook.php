<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use LiquidLight\Anthology\Hook\AbstractConfigurationHook;

class PluginConfigurationHook extends AbstractConfigurationHook
{
	public function getTcas(array &$params): void
	{
		$repositories = $this->repositoryFactory->getRepositories();

		$tcaConfigurations = array_filter(
			$GLOBALS['TCA'],
			fn ($tcaTableName) => in_array(
				$tcaTableName,
				array_keys($repositories)
			),
			ARRAY_FILTER_USE_KEY
		);

		$params['items'] = array_map(
			fn ($tableName, $tcaConfiguration) => [
				'value' => $tableName,
				'label' => $tcaConfiguration['ctrl']['title'],
				'icon' => $tcaConfiguration['ctrl']['iconfile'] ?? '',
			],
			array_keys($tcaConfigurations),
			$tcaConfigurations
		);
	}

	public function getRepositories(array &$params): void
	{
		$repositories = $this->repositoryFactory->getRepositories();

		$tcaConfigurations = array_filter(
			$GLOBALS['TCA'],
			fn ($tcaTableName) => in_array(
				$tcaTableName,
				array_keys($repositories)
			),
			ARRAY_FILTER_USE_KEY
		);

		$params['items'] = array_map(
			fn ($tableName, $tcaConfiguration) => [
				'value' => $repositories[$tableName],
				'label' => $tcaConfiguration['ctrl']['title'],
				'icon' => $tcaConfiguration['ctrl']['iconfile'] ?? '',
			],
			array_keys($tcaConfigurations),
			$tcaConfigurations
		);
	}

	public function getSortFields(array &$params): void
	{
		$this->getFields($params, [static::ALL_TYPES], $params['row']['uid']);

		$params['items'] = [
			[
				'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_be.xlf:source.sortBy.default',
				'value' => null,
			],
			...$params['items'],
		];
	}
}
