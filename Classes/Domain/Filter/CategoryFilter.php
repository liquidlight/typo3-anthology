<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Filter;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Domain\Filter\AbstractFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Model\Filter;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

#[AsAnthologyFilter('llanthology_category')]
class CategoryFilter extends AbstractFilter implements FilterInterface
{
	protected const LABEL = 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.category';

	public static function getOptions(
		Filter $filter,
		array $pluginSettings
	): array {
		$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
		$pageRepository = GeneralUtility::makeInstance(PageRepository::class);

		$queryBuilder = $connectionPool->getQueryBuilderForTable('sys_category');

		$queryBuilder
			->select('uid')
			->from('sys_category')
			->where(
				$queryBuilder->expr()->eq(
					'parent',
					$queryBuilder->createNamedParameter(
						$filter->getParsedSettings()['category'] ?? 0,
						Connection::PARAM_INT
					)
				)
			)
			->orderBy('sorting')
		;

		$childCategoryUids = array_column(
			$queryBuilder->executeQuery()->fetchAllAssociative(),
			'uid'
		);

		return

		array_map(
			fn ($category) => [
				'value' => $category['uid'],
				'title' => $category['title'],
			],
			array_map(
				fn ($childCategoryUid) => $pageRepository
					->getLanguageOverlay(
						'sys_category',
						$pageRepository->getRawRecord(
							'sys_category',
							$childCategoryUid
						)
					),
				$childCategoryUids
			)
		);
	}

	public static function getConstraint(
		Filter $filter,
		QueryInterface $query
	): ?ComparisonInterface {
		return !empty($filter->getParameter())
			? $query->in('categories.uid', [$filter->getParameter()])
			: null;
	}
}
