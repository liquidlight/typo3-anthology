<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Factory;

use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\PreFilterFactory;
use LiquidLight\Anthology\Registry\AnthologyFilterRegistry;
use LiquidLight\Anthology\Registry\AnthologyPreFilterRegistry;
use LiquidLight\Anthology\Tests\Fixtures\Filter\Both;
use LiquidLight\Anthology\Tests\Fixtures\Filter\FilterOnly;
use LiquidLight\Anthology\Tests\Fixtures\Filter\PreFilterOnly;
use PHPUnit\Framework\TestCase;
use stdClass;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @covers \LiquidLight\Anthology\Factory\PreFilterFactory
 * @covers \LiquidLight\Anthology\Factory\FilterFactory
 * @covers \LiquidLight\Anthology\Factory\AbstractFilterFactory
 */
class PreFilterFactoryTest extends TestCase
{
	public function testPreFilterFactoryKeysClassesByPreFilterType(): void
	{
		$registry = new AnthologyPreFilterRegistry();
		$registry->add(Both::class);
		$registry->add(PreFilterOnly::class);

		self::assertSame(
			['both' => Both::class, 'pre_filter_only' => PreFilterOnly::class],
			(new PreFilterFactory($registry))->getFilters()
		);
	}

	public function testFilterFactoryKeysClassesByFilterType(): void
	{
		$registry = new AnthologyFilterRegistry();
		$registry->add(FilterOnly::class);
		$registry->add(Both::class);

		self::assertSame(
			['filter_only' => FilterOnly::class, 'both' => Both::class],
			(new FilterFactory($registry))->getFilters()
		);
	}

	public function testGetConstraintsThrowsForUnregisteredType(): void
	{
		$subject = new PreFilterFactory(new AnthologyPreFilterRegistry());
		$filter = new stdClass();
		$filter->filterType = 'missing';

		$this->expectException(NotImplementedException::class);

		$subject->getConstraints([$filter], $this->createMock(QueryInterface::class));
	}
}
