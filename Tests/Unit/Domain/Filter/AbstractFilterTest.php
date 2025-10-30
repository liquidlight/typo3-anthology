<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Domain\Filter;

use LiquidLight\Anthology\Domain\Filter\AbstractFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Model\Filter;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @covers \LiquidLight\Anthology\Domain\Filter\AbstractFilter
 */
class AbstractFilterTest extends TestCase
{
	public function testImplementsFilterInterface(): void
	{
		$reflection = new \ReflectionClass(AbstractFilter::class);
		self::assertTrue($reflection->implementsInterface(FilterInterface::class));
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\AbstractFilter::getLabel
	 */
	public function testGetLabelReturnsConstantValue(): void
	{
		$testFilter = new class () extends AbstractFilter {
			protected const LABEL = 'test.label';

			public static function getConstraint(Filter $filter, QueryInterface $queryInterface): ComparisonInterface|ConstraintInterface|null
			{
				return null;
			}
		};

		self::assertSame('test.label', $testFilter::getLabel());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\AbstractFilter::getOptions
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter
	 */
	public function testGetOptionsReturnsEmptyArrayByDefault(): void
	{
		$testFilter = new class () extends AbstractFilter {
			protected const LABEL = 'test.label';

			public static function getConstraint(Filter $filter, QueryInterface $queryInterface): ComparisonInterface|ConstraintInterface|null
			{
				return null;
			}
		};

		$filter = new Filter();
		$pluginSettings = ['some' => 'settings'];

		$result = $testFilter::getOptions($filter, $pluginSettings);

		self::assertSame([], $result);
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Filter\AbstractFilter::getOptions
	 * @uses \LiquidLight\Anthology\Domain\Model\Filter
	 */
	public function testGetOptionsCanBeOverridden(): void
	{
		$testFilter = new class () extends AbstractFilter {
			protected const LABEL = 'test.label';

			public static function getOptions(Filter $filter, array $pluginSettings): array
			{
				return ['custom' => 'options'];
			}

			public static function getConstraint(Filter $filter, QueryInterface $queryInterface): ComparisonInterface|ConstraintInterface|null
			{
				return null;
			}
		};

		$filter = new Filter();
		$pluginSettings = [];

		$result = $testFilter::getOptions($filter, $pluginSettings);

		self::assertSame(['custom' => 'options'], $result);
	}
}
