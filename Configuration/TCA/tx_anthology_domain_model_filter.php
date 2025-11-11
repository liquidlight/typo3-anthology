<?php

use LiquidLight\Anthology\Hook\FilterConfigurationHook;

return [
	'ctrl' => [
		'title' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter',
		'label' => 'title',
		'label_alt' => 'filter_type',
		'label_alt_force' => true,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		],
		'type' => 'filter_type',
		'iconfile' => 'EXT:ll_anthology/Resources/Public/Icons/Filter.svg',
		'security' => [
			'ignorePageTypeRestriction' => true,
		],
	],
	'columns' => [
		// General
		'filter_type' => [
			'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'itemsProcFunc' => FilterConfigurationHook::class . '->getAvailableFilters',
				'default' => 0,
			],
		],
		'title' => [
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.title',
			'displayCond' => 'FIELD:filter_type:!=:0',
			'config' => [
				'type' => 'input',
				'eval' => 'trim',
				'required' => true,
			],
		],
		'settings' => [
			'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_be.xlf:settings',
			'displayCond' => 'FIELD:filter_type:!=:0',
			'config' => [
				'type' => 'flex',
				/**
				 * This will break in v14, but the v14 way of implementing this
				 * feature is not yet available
				 *
				 * @see https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.0/Breaking-107047-RemovePointerFieldFunctionalityOfTCAFlex.html#breaking-107047-1751982363
				 */
				'ds_pointerField' => 'filter_type',
				'ds' => [
					'default' => 'FILE:EXT:ll_anthology/Configuration/FlexForms/Filter/Default.xml',
					'llanthology_search' => 'FILE:EXT:ll_anthology/Configuration/FlexForms/Filter/Search.xml',
					'llanthology_category' => 'FILE:EXT:ll_anthology/Configuration/FlexForms/Filter/Category.xml',
					'llanthology_date' => 'FILE:EXT:ll_anthology/Configuration/FlexForms/Filter/Date.xml',
				],
			],
		],

		// Access
		'hidden' => [
			'exclude' => true,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
			'config' => [
				'type' => 'check',
				'renderType' => 'checkboxToggle',
				'items' => [
					[
						'label' => '',
						'value' => '',
						'invertStateDisplay' => true,
					],
				],
			],
		],
		'starttime' => [
			'exclude' => true,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
			'config' => [
				'type' => 'datetime',
				'format' => 'datetime',
				'default' => 0,
			],
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		],
		'endtime' => [
			'exclude' => true,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
			'config' => [
				'type' => 'datetime',
				'format' => 'datetime',
				'default' => 0,
				'range' => [
					'upper' => 2145916800,
				],
			],
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
		],
		'fe_group' => [
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
			'l10n_mode' => 'exclude',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'exclusiveKeys' => '-1,-2',
				'items' => [
					['label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login', 'value' => -1],
					['label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login', 'value' => -2],
					['label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups', 'value' => '--div--'],
				],
				'foreign_table' => 'fe_groups',
			],
		],
	],
	'palettes' => [
		'start_end' => [
			'label' => '',
			'showitem' => '
				starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,
				endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,
			',
		],
		'general' => [
			'label' => '',
			'showitem' => '
				filter_type,
				--linebreak--,
				title,
				--linebreak--,
				settings,
			',
		],
	],
	'types' => [
		0 => [
			'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
					--palette--;;general,
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden,
					--palette--;;start_end,
					fe_group,
			',
		],
	],
];
