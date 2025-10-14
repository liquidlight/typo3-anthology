<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Query;

use LiquidLight\Anthology\Domain\Model\Filter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

interface FilterConstraintInterface
{
	public static function getConstraint(
		Filter $filter,
		QueryInterface $queryInterface
	): ComparisonInterface|ConstraintInterface;
}
