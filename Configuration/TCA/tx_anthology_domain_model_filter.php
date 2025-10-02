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
				'items' => [
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.please_select',
						'value' => 0,
					],
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.search',
						'value' => 'search',
					],
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.category',
						'value' => 'category',
					],
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.date',
						'value' => 'date',
					],
				],
				'default' => 0,
			],
		],
		'title' => [
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.title',
			'config' => [
				'type' => 'input',
				'eval' => 'trim',
				'required' => true,
			],
		],
		'display_mode' => [
			'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.display_mode',
			'displayCond' => 'FIELD:filter_type:!=:search',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.display_mode.select',
						'value' => 'select',
					],
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.display_mode.link',
						'value' => 'link',
					],
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.display_mode.check',
						'value' => 'check',
					],
				],
			],
		],

		// Search
		'search_fields' => [
			'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.search_fields',
			'description' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.search_fields.description',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'itemsProcFunc' => FilterConfigurationHook::class . '->getSearchFields',
				'minitems' => 1,
			],
		],
		'placeholder' => [
			'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.placeholder',
			'config' => [
				'type' => 'input',
				'eval' => 'trim',
			],
		],

		// Category
		'category' => [
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.category',
			'description' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.category.description',
			'config' => [
				'type' => 'category',
				'minitems' => 1,
				'maxitems' => 1,
			],
		],

		// Date
		'date_field' => [
			'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.date_field',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'itemsProcFunc' => FilterConfigurationHook::class . '->getDateFields',
			],
		],
		'date_span' => [
			'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.date_span',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.date_span.months',
						'value' => 'months',
					],
					[
						'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.date_span.years',
						'value' => 'years',
					],
				],
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
				display_mode,
			',
		],
		'date_filter_options' => [
			'label' => '',
			'showitem' => '
				date_field,
				date_span,
			',
		],
	],
	'types' => [
		0 => [ // Search
			'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
					filter_type,
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden,
					--palette--;;start_end,
					fe_group,
			',
		],
		'search' => [ // Search
			'showitem' => '
			--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
			--palette--;;general,
			search_fields,
			placeholder,
			--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
			hidden,
			--palette--;;start_end,
			fe_group,
			',
		],
		'category' => [ // Category
			'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
					--palette--;;general,
					category,
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden,
					--palette--;;start_end,
					fe_group,
			',
		],
		'date' => [ // Date
			'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
					--palette--;;general,
					--palette--;;date_filter_options,
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden,
					--palette--;;start_end,
					fe_group,
			',
		],
	],
];
