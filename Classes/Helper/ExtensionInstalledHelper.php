<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Helper;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionInstalledHelper
{
	public function isInstalled(array $params): bool {
		if (empty($params['conditionParameters'][0])) {
			return false;
		}

		return GeneralUtility::makeInstance(ExtensionManagementUtility::class)
			->isLoaded($params['conditionParameters'][0])
		;
	}
}
