<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use LiquidLight\Anthology\Factory\RepositoryFactory;

class PluginConfigurationHook
{
	public function __construct(
		private RepositoryFactory $repositoryFactory
	) {
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
				'value' => $tableName,
				'label' => $tcaConfiguration['ctrl']['title'],
				'icon' => $tcaConfiguration['ctrl']['iconfile'] ?? '',
			],
			array_keys($tcaConfigurations),
			$tcaConfigurations
		);
	}
}
