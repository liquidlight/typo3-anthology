<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

use LiquidLight\Anthology\Attribute\AsAnthologyPreFilter;
use LiquidLight\Anthology\Hook\AbstractConfigurationHook;
use ReflectionClass;

class FilterConfigurationHook extends AbstractConfigurationHook
{
	private const SEARCH_FIELD_TYPES = [
		'input',
		'text',
	];

	private const DATE_FIELD_TYPES = [
		'datetime',
	];

	private const PRE_FILTERS_FIELD_NAME = 'settings.preFilters';

	public function getAvailableFilters(array &$params): void
	{
		$filters = $this->filterFactory->getFilters();

		if (($params['inlineParentFieldName'] ?? null) === self::PRE_FILTERS_FIELD_NAME) {
			$filters = array_filter(
				$filters,
				fn (string $filterClass) => (new ReflectionClass($filterClass))->getAttributes(AsAnthologyPreFilter::class) != []
			);
		}

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
