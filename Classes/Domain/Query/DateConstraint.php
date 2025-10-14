<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Query;

use LiquidLight\Anthology\Domain\Model\Filter;
use LiquidLight\Anthology\Domain\Query\FilterConstraintInterface;
use TYPO3\CMS\Core\Utility\Exception\NotImplementedMethodException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class DateConstraint implements FilterConstraintInterface
{
	public static function getConstraint(
		Filter $filter,
		QueryInterface $query
	): ComparisonInterface {
		throw new NotImplementedMethodException();
	}
}
