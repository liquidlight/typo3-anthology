<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Domain\Filter;

use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Filter\SearchFilter;
use LiquidLight\Anthology\Domain\Model\Filter;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @covers \LiquidLight\Anthology\Domain\Filter\SearchFilter
 */
class SearchFilterTest extends TestCase
{
	public function testImplementsFilterInterface(): void
	{
		$reflection = new \ReflectionClass(SearchFilter::class);
		self::assertTrue($reflection->implementsInterface(FilterInterface::class));
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\SearchFilter::getLabel
	 */
	public function testGetLabelReturnsExpectedValue(): void
	{
		$expectedLabel = 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.search';

		self::assertSame($expectedLabel, SearchFilter::getLabel());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\SearchFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintReturnsNullWhenParameterIsEmpty(): void
	{
		$filter = new Filter();
		$filter->setParameter('');

		$queryMock = $this->createMock(QueryInterface::class);

		$result = SearchFilter::getConstraint($filter, $queryMock);

		self::assertNull($result);
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\SearchFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintReturnsNullWhenParameterIsNull(): void
	{
		$filter = new Filter();
		$filter->setParameter(null);

		$queryMock = $this->createMock(QueryInterface::class);

		$result = SearchFilter::getConstraint($filter, $queryMock);

		self::assertNull($result);
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\SearchFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintReturnsNullWhenParameterIsZero(): void
	{
		$filter = new Filter();
		$filter->setParameter(0);

		$queryMock = $this->createMock(QueryInterface::class);

		$result = SearchFilter::getConstraint($filter, $queryMock);

		self::assertNull($result);
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\SearchFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintReturnsNullWhenParameterIsFalse(): void
	{
		$filter = new Filter();
		$filter->setParameter(false);

		$queryMock = $this->createMock(QueryInterface::class);

		$result = SearchFilter::getConstraint($filter, $queryMock);

		self::assertNull($result);
	}
}
