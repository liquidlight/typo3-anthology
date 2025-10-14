<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Query;

use LiquidLight\Anthology\Domain\Model\Filter;
use LiquidLight\Anthology\Domain\Query\FilterConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class SearchConstraint implements FilterConstraintInterface
{
	public static function getConstraint(
		Filter $filter,
		QueryInterface $query
	): ConstraintInterface {
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
}
