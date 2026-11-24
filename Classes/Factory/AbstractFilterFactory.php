<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Factory;

use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Registry\AbstractAnthologyRegistry;
use ReflectionClass;
use RuntimeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

abstract class AbstractFilterFactory
{
	protected array $filters = [];

	public function getFilters(): array
	{
		if ($this->filters !== []) {
			return $this->filters;
		}

		$filterClasses = $this->getRegistry()->get();
		$attributeClass = $this->getAttributeClass();

		$this->filters = array_combine(
			array_map(
				function ($filterClass) use ($attributeClass) {
					$reflectionClass = new ReflectionClass($filterClass);

					foreach ($reflectionClass->getAttributes($attributeClass) as $filterAttribute) {
						return $filterAttribute->newInstance()->filterType;
					}
				},
				$filterClasses
			),
			$filterClasses
		);

		return $this->filters;
	}

	public function getConstraints(
		iterable $filters,
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

	abstract protected function getRegistry(): AbstractAnthologyRegistry;

	/**
	 * The attribute class (exposing a `filterType` property) used to
	 * identify filters in the registry
	 */
	abstract protected function getAttributeClass(): string;
}
