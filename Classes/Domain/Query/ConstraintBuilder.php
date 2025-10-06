<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Query;

use LiquidLight\Anthology\Domain\Model\Filter;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class ConstraintBuilder
{
	public function getConstraints(QueryResultInterface $filters, QueryInterface $query): array
	{
		$constraints = [];

		foreach ($filters as $filter) {
			switch ($filter->filterType) {
				case 'search':
					$constraints[] = $this->getSearchConstraint($filter, $query);
					break;

				case 'category':
					$constraints[] = $this->getCategoryConstraint($filter, $query);
					break;

				case 'date':
					break;

				default:
					throw new NotImplementedException(
						sprintf(
							'Filter of type `%s` is not available',
							$filter->filterType
						),
						1759767112
					);
			}
		}

		return $constraints;
	}

	private function getSearchConstraint(Filter $filter, QueryInterface $query): OrInterface
	{
		return $query->logicalOr(
			...array_map(
				fn ($searchField) => $query->like(
					$searchField,
					sprintf(
						'%%%s%%',
						$filter->getValue()
					)
				),
				$filter->getSearchFields()
			)
		);
	}

	private function getCategoryConstraint(Filter $filter, QueryInterface $query): ComparisonInterface
	{
		return $query->in('categories.uid', [$filter->getValue()]);
	}
}
