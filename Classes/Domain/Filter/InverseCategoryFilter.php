<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Filter;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Attribute\AsAnthologyPreFilter;
use LiquidLight\Anthology\Domain\Model\Filter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

#[AsAnthologyFilter('llanthology_inversecategory')]
#[AsAnthologyPreFilter]
class InverseCategoryFilter extends CategoryFilter
{
	protected const LABEL = 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.inverse_category';

	public static function getConstraint(
		Filter $filter,
		QueryInterface $query
	): ?ConstraintInterface {
		/**
		 * Not including records with specific categories is not quite as simple
		 * as negating the category, as any record with other categories will
		 * still match when using `$query->logicalNot(parent::getConstraint(...))`
		 */

		// Perform a standalone query which matches all the records matching the specified category
		$excludeQuery = GeneralUtility::makeInstance(PersistenceManagerInterface::class)
			->createQueryForType($query->getType())
		;

		$categoryConstraint = parent::getConstraint($filter, $excludeQuery);

		if (!$categoryConstraint) {
			return null;
		}

		$excludeQuery->setQuerySettings(clone $query->getQuerySettings());
		$excludeQuery->matching($categoryConstraint);

		// Collect the UIDs of matching records
		$uids = array_column($excludeQuery->execute(true), 'uid');

		if ($uids === []) {
			return null;
		}

		// Explicitly exclude the matching records from the returned query
		return $query->logicalNot(
			$query->in('uid', $uids)
		);
	}
}
