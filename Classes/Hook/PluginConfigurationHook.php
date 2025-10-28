<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class PluginConfigurationHook
{
	private array $typoScript;

	public function __construct(
		private ConfigurationManager $configurationManager,
		private TypoScriptService $typoScriptService
	) {
		$this->typoScript = $this->typoScriptService->convertTypoScriptArrayToPlainArray(
			$this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT)
		);
	}

	public function getRepositories(array &$params): void
	{
		$repositories = $this->typoScript['plugin']['tx_llanthology']['settings']['repositories'] ?? [];

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
