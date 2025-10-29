<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Query;

use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use RuntimeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class ConstraintBuilder
{
	public function getConstraints(
		QueryResultInterface $filters,
		QueryInterface $query,
		array $filterImplementations
	): array {
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

		return $constraints;
	}
}
