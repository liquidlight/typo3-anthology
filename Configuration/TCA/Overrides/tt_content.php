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

	// Add FlexForm to the plugin
	$pluginSignature = 'llanthology_anthologyview';

	// Add pi_flexform to the plugin
	$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

	// Add the FlexForm configuration
	ExtensionManagementUtility::addPiFlexFormValue(
		$pluginSignature,
		'FILE:EXT:ll_anthology/Configuration/FlexForms/ViewPlugin.xml'
	);
});
