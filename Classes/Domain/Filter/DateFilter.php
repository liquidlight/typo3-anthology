<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Filter;

use LiquidLight\Anthology\Domain\Filter\AbstractFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Model\Filter;
use TYPO3\CMS\Core\Utility\Exception\NotImplementedMethodException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class DateFilter extends AbstractFilter implements FilterInterface
{
	protected const LABEL = 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.date';

	public static function getConstraint(
		Filter $filter,
		QueryInterface $query
	): ComparisonInterface|ConstraintInterface {
		throw new NotImplementedMethodException();
	}
}
