<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use LiquidLight\Anthology\Hook\AbstractConfigurationHook;

class FilterConfigurationHook extends AbstractConfigurationHook
{
	private const SEARCH_FIELD_TYPES = [
		'input',
		'text',
	];

	private const DATE_FIELD_TYPES = [
		'datetime',
	];

	public function getAvailableFilters(array &$params): void
	{
		$filters = $this->filterFactory->getFilters();

		$filterItems = array_map(
			fn ($filterType, $filterClass) => [
				'label' => $filterClass::getLabel(),
				'value' => $filterType,
			],
			array_keys($filters),
			$filters
		);

		usort($filterItems, fn ($a, $b) => $a['label'] <=> $b['label']);

		$params['items'] = [
			[
				'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.please_select',
				'value' => 0,
			],
			...$filterItems,
		];
	}

	public function getSearchFields(array &$params): void
	{
		$this->getFields($params, self::SEARCH_FIELD_TYPES);
	}

	public function getDateFields(array &$params): void
	{
		$this->getFields($params, self::DATE_FIELD_TYPES);
	}
}
