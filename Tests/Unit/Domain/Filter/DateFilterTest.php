<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Domain\Filter;

use LiquidLight\Anthology\Domain\Filter\DateFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Model\Filter;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @covers \LiquidLight\Anthology\Domain\Filter\DateFilter
 */
class DateFilterTest extends TestCase
{
	public function testImplementsFilterInterface(): void
	{
		$reflection = new \ReflectionClass(DateFilter::class);
		self::assertTrue($reflection->implementsInterface(FilterInterface::class));
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\DateFilter::getLabel
	 */
	public function testGetLabelReturnsExpectedValue(): void
	{
		$expectedLabel = 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.date';

		self::assertSame($expectedLabel, DateFilter::getLabel());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\DateFilter::getConstraint
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 */
	public function testGetConstraintMethodExists(): void
	{
		$reflection = new \ReflectionClass(DateFilter::class);
		self::assertTrue($reflection->hasMethod('getConstraint'));

		$method = $reflection->getMethod('getConstraint');
		$parameters = $method->getParameters();

		self::assertCount(2, $parameters);
		self::assertSame('filter', $parameters[0]->getName());
		self::assertSame('query', $parameters[1]->getName());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\DateFilter::getOptions
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter
	 */
	public function testGetOptionsMethodExists(): void
	{
		$reflection = new \ReflectionClass(DateFilter::class);
		self::assertTrue($reflection->hasMethod('getOptions'));

		$method = $reflection->getMethod('getOptions');
		$parameters = $method->getParameters();

		self::assertCount(2, $parameters);
		self::assertSame('filter', $parameters[0]->getName());
		self::assertSame('pluginSettings', $parameters[1]->getName());
	}

	public function testDateFilterHasExpectedConstants(): void
	{
		$reflection = new \ReflectionClass(DateFilter::class);

		// Test that the class has the expected structure
		self::assertTrue($reflection->hasConstant('LABEL'));

		// Test that it has the expected private methods for relative/bound handling
		self::assertTrue($reflection->hasMethod('getRelativeOptions'));
		self::assertTrue($reflection->hasMethod('getBoundOptions'));
		self::assertTrue($reflection->hasMethod('getRelativeConstraint'));
		self::assertTrue($reflection->hasMethod('getBoundConstraint'));
	}

	public function testDateFilterMethodsAreStatic(): void
	{
		$reflection = new \ReflectionClass(DateFilter::class);

		// Test that the main public methods are static (as required by FilterInterface)
		$getConstraintMethod = $reflection->getMethod('getConstraint');
		self::assertTrue($getConstraintMethod->isStatic());

		$getOptionsMethod = $reflection->getMethod('getOptions');
		self::assertTrue($getOptionsMethod->isStatic());

		$getLabelMethod = $reflection->getMethod('getLabel');
		self::assertTrue($getLabelMethod->isStatic());
	}

	public function testDateFilterPrivateMethodsAreStatic(): void
	{
		$reflection = new \ReflectionClass(DateFilter::class);

		// Test that the private helper methods are also static
		$getRelativeOptionsMethod = $reflection->getMethod('getRelativeOptions');
		self::assertTrue($getRelativeOptionsMethod->isStatic());

		$getBoundOptionsMethod = $reflection->getMethod('getBoundOptions');
		self::assertTrue($getBoundOptionsMethod->isStatic());

		$getRelativeConstraintMethod = $reflection->getMethod('getRelativeConstraint');
		self::assertTrue($getRelativeConstraintMethod->isStatic());

		$getBoundConstraintMethod = $reflection->getMethod('getBoundConstraint');
		self::assertTrue($getBoundConstraintMethod->isStatic());
	}

	public function testDateFilterHasCorrectReturnTypes(): void
	{
		$reflection = new \ReflectionClass(DateFilter::class);

		// Test getConstraint return type
		$getConstraintMethod = $reflection->getMethod('getConstraint');
		$returnType = $getConstraintMethod->getReturnType();
		self::assertNotNull($returnType);

		// Test getOptions return type
		$getOptionsMethod = $reflection->getMethod('getOptions');
		$returnType = $getOptionsMethod->getReturnType();
		self::assertNotNull($returnType);
		self::assertSame('array', (string)$returnType);
	}
}
