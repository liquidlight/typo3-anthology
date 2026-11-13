<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Domain\Filter;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Attribute\AsAnthologyPreFilter;
use LiquidLight\Anthology\Domain\Filter\CategoryFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Filter\InverseCategoryFilter;
use LiquidLight\Anthology\Domain\Model\Filter;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @covers \LiquidLight\Anthology\Domain\Filter\InverseCategoryFilter
 */
class InverseCategoryFilterTest extends TestCase
{
	protected function tearDown(): void
	{
		GeneralUtility::purgeInstances();
	}

	public function testImplementsFilterInterface(): void
	{
		$reflection = new \ReflectionClass(InverseCategoryFilter::class);
		self::assertTrue($reflection->implementsInterface(FilterInterface::class));
	}

	public function testExtendsCategoryFilter(): void
	{
		self::assertTrue(is_subclass_of(InverseCategoryFilter::class, CategoryFilter::class));
	}

	public function testIsRegisteredAsFilterAndPreFilter(): void
	{
		$reflection = new \ReflectionClass(InverseCategoryFilter::class);

		$filterAttributes = $reflection->getAttributes(AsAnthologyFilter::class);

		self::assertCount(1, $filterAttributes);
		self::assertSame('llanthology_inversecategory', $filterAttributes[0]->newInstance()->filterType);
		self::assertCount(1, $reflection->getAttributes(AsAnthologyPreFilter::class));
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\InverseCategoryFilter::getLabel
	 */
	public function testGetLabelReturnsExpectedValue(): void
	{
		self::assertSame(
			'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.inverse_category',
			InverseCategoryFilter::getLabel()
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\InverseCategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Filter\CategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintReturnsNullWhenParameterIsEmpty(): void
	{
		$filter = new Filter();
		$filter->setParameter('');

		$excludeQuery = $this->createMock(QueryInterface::class);
		$excludeQuery->expects(self::never())->method('execute');

		$this->mockPersistenceManager($excludeQuery);

		$queryMock = $this->createMock(QueryInterface::class);
		$queryMock->method('getType')->willReturn('Some\\Model');

		self::assertNull(InverseCategoryFilter::getConstraint($filter, $queryMock));
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\InverseCategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Filter\CategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintReturnsNullWhenNoRecordsMatchCategory(): void
	{
		$filter = new Filter();
		$filter->setParameter('5');

		$excludeQuery = $this->createExcludeQuery([]);
		$this->mockPersistenceManager($excludeQuery);

		$queryMock = $this->createMock(QueryInterface::class);
		$queryMock->method('getType')->willReturn('Some\\Model');
		$queryMock->method('getQuerySettings')->willReturn($this->createMock(QuerySettingsInterface::class));
		$queryMock->expects(self::never())->method('logicalNot');

		self::assertNull(InverseCategoryFilter::getConstraint($filter, $queryMock));
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\InverseCategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Filter\CategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintExcludesRecordsMatchingCategory(): void
	{
		$filter = new Filter();
		$filter->setParameter('5');

		$excludeQuery = $this->createExcludeQuery([['uid' => 1], ['uid' => 3]]);
		$this->mockPersistenceManager($excludeQuery);

		$inMock = $this->createMock(ComparisonInterface::class);
		$notMock = $this->createMock(ConstraintInterface::class);

		$queryMock = $this->createMock(QueryInterface::class);
		$queryMock->method('getType')->willReturn('Some\\Model');
		$queryMock->method('getQuerySettings')->willReturn($this->createMock(QuerySettingsInterface::class));
		$queryMock
			->expects(self::once())
			->method('in')
			->with('uid', [1, 3])
			->willReturn($inMock)
		;
		$queryMock
			->expects(self::once())
			->method('logicalNot')
			->with($inMock)
			->willReturn($notMock)
		;

		self::assertSame($notMock, InverseCategoryFilter::getConstraint($filter, $queryMock));
	}

	/**
	 * Build the standalone query used to find the records to exclude
	 */
	private function createExcludeQuery(array $rows): QueryInterface
	{
		$excludeQuery = $this->createMock(QueryInterface::class);
		$excludeQuery->method('in')->willReturn($this->createMock(ComparisonInterface::class));
		$excludeQuery->method('execute')->with(true)->willReturn($rows);

		return $excludeQuery;
	}

	private function mockPersistenceManager(QueryInterface $excludeQuery): void
	{
		$persistenceManager = $this->createMockForIntersectionOfInterfaces([
			PersistenceManagerInterface::class,
			SingletonInterface::class,
		]);
		$persistenceManager
			->method('createQueryForType')
			->with('Some\\Model')
			->willReturn($excludeQuery)
		;

		GeneralUtility::setSingletonInstance(PersistenceManagerInterface::class, $persistenceManager);
	}
}
