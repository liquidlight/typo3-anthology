<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Domain\Filter;

use LiquidLight\Anthology\Domain\Filter\CategoryFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Model\Filter;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @covers \LiquidLight\Anthology\Domain\Filter\CategoryFilter
 */
class CategoryFilterTest extends TestCase
{
	public function testImplementsFilterInterface(): void
	{
		$reflection = new \ReflectionClass(CategoryFilter::class);
		self::assertTrue($reflection->implementsInterface(FilterInterface::class));
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\CategoryFilter::getLabel
	 */
	public function testGetLabelReturnsExpectedValue(): void
	{
		$expectedLabel = 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.category';

		self::assertSame($expectedLabel, CategoryFilter::getLabel());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\CategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintReturnsNullWhenParameterIsEmpty(): void
	{
		$filter = new Filter();
		$filter->setParameter('');

		$queryMock = $this->createMock(QueryInterface::class);

		$result = CategoryFilter::getConstraint($filter, $queryMock);

		self::assertNull($result);
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\CategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintReturnsNullWhenParameterIsNull(): void
	{
		$filter = new Filter();
		$filter->setParameter(null);

		$queryMock = $this->createMock(QueryInterface::class);

		$result = CategoryFilter::getConstraint($filter, $queryMock);

		self::assertNull($result);
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\CategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintCreatesInConstraintWithCategoryParameter(): void
	{
		$filter = new Filter();
		$filter->setParameter('123');

		$comparisonMock = $this->createMock(ComparisonInterface::class);

		$queryMock = $this->createMock(QueryInterface::class);
		$queryMock
			->expects(self::once())
			->method('in')
			->with('categories.uid', ['123'])
			->willReturn($comparisonMock)
		;

		$result = CategoryFilter::getConstraint($filter, $queryMock);

		self::assertSame($comparisonMock, $result);
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\CategoryFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintCreatesInConstraintWithIntegerParameter(): void
	{
		$filter = new Filter();
		$filter->setParameter(456);

		$comparisonMock = $this->createMock(ComparisonInterface::class);

		$queryMock = $this->createMock(QueryInterface::class);
		$queryMock
			->expects(self::once())
			->method('in')
			->with('categories.uid', [456])
			->willReturn($comparisonMock)
		;

		$result = CategoryFilter::getConstraint($filter, $queryMock);

		self::assertSame($comparisonMock, $result);
	}
}
