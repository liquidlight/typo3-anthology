<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Filter;

use LiquidLight\Anthology\Domain\Filter\AbstractFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Model\Filter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class SearchFilter extends AbstractFilter implements FilterInterface
{
	protected const LABEL = 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.search';

	public static function getConstraint(
		Filter $filter,
		QueryInterface $query
	): ?ConstraintInterface {
		return !empty($filter->getParameter())
			? $query->logicalOr(
				...array_map(
					fn ($searchField) => $query->like(
						$searchField,
						sprintf(
							'%%%s%%',
							$filter->getParameter()
						)
					),
					GeneralUtility::trimExplode(',', $filter->getParsedSettings()['searchFields'] ?? '')
				)
			)
			: null;
	}
}
