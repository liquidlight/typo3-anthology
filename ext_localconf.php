<?php

declare(strict_types=1);

use LiquidLight\Anthology\Controller\AnthologyController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

call_user_func(function () {
	ExtensionUtility::configurePlugin(
		'LlAnthology',
		'AnthologyView',
		[
			AnthologyController::class => 'view,list,single',
		],
		[
			AnthologyController::class => 'view,list',
		]
	);

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['llanthology_cache']
		??= [];
});
