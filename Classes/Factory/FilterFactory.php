<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Factory;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Registry\AnthologyFilterRegistry;
use ReflectionClass;
use RuntimeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class FilterFactory
{
	public function __construct(
		private readonly AnthologyFilterRegistry $filterRegistry
	) {
	}

	public function getFilters(): array
	{
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['ll_anthology']['filters'])) {
			trigger_error(
				'Manually specifying Anthology filters has been removed, this configuration will be ignored',
				E_USER_DEPRECATED
			);
		}

		$filterClasses = $this->filterRegistry->get();

		$filters = array_combine(
			array_map(
				function ($filterClass) {
					$reflectionClass = new ReflectionClass($filterClass);

					foreach ($reflectionClass->getAttributes(AsAnthologyFilter::class) as $filterAttribute) {
						return $filterAttribute->newInstance()->filterType;
					}
				},
				$filterClasses
			),
			$filterClasses
		);

		return $filters;
	}

	public function getConstraints(
		QueryResultInterface $filters,
		QueryInterface $query
	): array {
		$filterImplementations = $this->getFilters();
		$constraints = [];

		foreach ($filters as $filter) {
			if (!isset($filterImplementations[$filter->filterType])) {
				throw new NotImplementedException(
					sprintf(
						'Filter of type `%s` is not available',
						$filter->filterType
					),
					1759767112
				);
			}

			if (!is_subclass_of($filterImplementations[$filter->filterType], FilterInterface::class)) {
				throw new RuntimeException(
					sprintf(
						'`%s` is not a valid implementation of `%s`',
						$filterImplementations[$filter->filterType],
						FilterInterface::class
					),
					1760448468
				);
			}

			$constraints[] = $filterImplementations[$filter->filterType]::getConstraint(
				$filter,
				$query
			);
		}

		return array_filter($constraints);
	}
}
