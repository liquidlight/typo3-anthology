<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Model;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Filter extends AbstractEntity
{
	public string $filterType = '';

	public string $title = '';

	public string $displayMode = '';

	public array $searchFields = [];

	public string $placeholder = '';

	public string $dateField = '';

	public string $dateSpan = '';

	public ?Category $category = null;

	protected mixed $value = null;

	public function getCategories(): array
	{
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
						$this->category->getUid(),
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

		return array_map(
			fn ($childCategoryUid) => $pageRepository
				->getLanguageOverlay(
					'sys_category',
					$pageRepository->getRawRecord(
						'sys_category',
						$childCategoryUid
					)
				),
			$childCategoryUids
		);
	}

	public function getValue(): mixed
	{
		return $this->value;
	}

	public function setValue(mixed $value): void
	{
		switch ($this->filterType) {
			case 'category':
				$this->value = (int)$value;
				break;

			default:
				$this->value = $value;
				break;
		}
	}
}
