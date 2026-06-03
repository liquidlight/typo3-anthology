<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

call_user_func(function () {
	// Register the plugin
	ExtensionUtility::registerPlugin(
		'LlAnthology',
		'AnthologyView',
		'LLL:EXT:ll_anthology/Resources/Private/Language/locallang.xlf:title',
		'ext-ll-anthology-plugin',
		'plugins',
		'LLL:EXT:ll_anthology/Resources/Private/Language/locallang.xlf:description'
	);

	$pluginSignature = 'llanthology_anthologyview';

	// Add pi_flexform & other fields to the plugin
	ExtensionManagementUtility::addToAllTCAtypes(
		'tt_content',
		'--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,
			pi_flexform,
			pages,
			recursive,
		',
		$pluginSignature,
		'after:header',
	);

	// Add the FlexForm configuration
	ExtensionManagementUtility::addPiFlexFormValue(
		'*',
		'FILE:EXT:ll_anthology/Configuration/FlexForms/ViewPlugin.xml',
		$pluginSignature,
	);
});
