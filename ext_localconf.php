<?php

declare(strict_types=1);

use LiquidLight\Anthology\Controller\AnthologyController;
use LiquidLight\Anthology\Form\Container\FlexFormElementContainer;
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
		],
		ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
	);

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1787755841] = [
		'nodeName' => 'flexFormElementContainer',
		'priority' => 40,
		'class' => FlexFormElementContainer::class,
	];
});
