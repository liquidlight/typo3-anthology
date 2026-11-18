<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Pagination;

use LiquidLight\Anthology\Pagination\PaginationState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LiquidLight\Anthology\Pagination\PaginationState
 */
class PaginationStateTest extends TestCase
{
	public function testDefaultsToPageOneForEveryPlugin(): void
	{
		$state = PaginationState::fromArguments(null, null);

		self::assertSame(1, $state->getPage(10, true));
		self::assertSame(1, $state->getPage(10, false));
		self::assertSame([], $state->toArguments());
	}

	public function testOwnerAndOtherPluginsArePaginatedIndependently(): void
	{
		$state = PaginationState::fromArguments('3', [20 => '2']);

		self::assertSame(3, $state->getPage(10, true));
		self::assertSame(2, $state->getPage(20, false));
		self::assertSame(1, $state->getPage(30, false));
	}

	#[DataProvider('invalidPageProvider')]
	public function testInvalidPagesFallBackToPageOne(mixed $invalid): void
	{
		$state = PaginationState::fromArguments($invalid, [20 => $invalid]);

		self::assertSame(1, $state->getPage(10, true));
		self::assertSame(1, $state->getPage(20, false));
		self::assertSame([], $state->toArguments());
	}

	public static function invalidPageProvider(): array
	{
		return [
			'zero' => [0],
			'negative' => [-4],
			'non numeric' => ['abc'],
			'array' => [[2]],
			'float' => [1.5],
		];
	}

	public function testMalformedOtherPluginKeysAreDropped(): void
	{
		$state = PaginationState::fromArguments(null, ['abc' => 2, 0 => 2, -1 => 2, 5 => 2]);

		self::assertSame(['pages' => [5 => 2]], $state->toArguments());
	}

	public function testNonArrayOthersAreIgnored(): void
	{
		self::assertSame([], PaginationState::fromArguments(null, 'oops')->toArguments());
	}

	public function testWithPageChangesOnlyTheRequestedPlugin(): void
	{
		$state = PaginationState::fromArguments(2, [20 => 3]);

		$ownerChanged = $state->withPage(10, true, 4);
		self::assertSame(4, $ownerChanged->getPage(10, true));
		self::assertSame(3, $ownerChanged->getPage(20, false));

		$otherChanged = $state->withPage(20, false, 5);
		self::assertSame(2, $otherChanged->getPage(10, true));
		self::assertSame(5, $otherChanged->getPage(20, false));
	}

	public function testStateIsImmutable(): void
	{
		$state = PaginationState::fromArguments(2, null);
		$state->withPage(10, true, 9);

		self::assertSame(2, $state->getPage(10, true));
	}

	public function testPageOneIsOmittedFromArguments(): void
	{
		$state = PaginationState::fromArguments(2, [20 => 3])
			->withPage(10, true, 1)
			->withPage(20, false, 1)
		;

		self::assertSame([], $state->toArguments());
	}

	public function testArgumentsAreSortedByContentUid(): void
	{
		$state = PaginationState::fromArguments(2, [30 => 2, 20 => 4]);

		self::assertSame(
			['currentPage' => 2, 'pages' => [20 => 4, 30 => 2]],
			$state->toArguments()
		);
		self::assertSame([20, 30], array_keys($state->toArguments()['pages']));
	}

	public function testPagesBelowOneAreClampedWhenSet(): void
	{
		$state = PaginationState::fromArguments(null, null)->withPage(10, true, -3);

		self::assertSame(1, $state->getPage(10, true));
	}

	public function testActiveFiltersAreKeptOnTheLink(): void
	{
		$state = PaginationState::fromArguments(null, null);

		self::assertSame(
			['filter' => [5 => '3']],
			$state->toArguments(['5' => '3'])
		);
	}

	public function testFiltersAreCombinedWithPages(): void
	{
		$state = PaginationState::fromArguments(2, [20 => 3]);

		self::assertSame(
			['currentPage' => 2, 'pages' => [20 => 3], 'filter' => [5 => ['a', 'b']]],
			$state->toArguments([5 => ['a', 'b']])
		);
	}

	public function testNoFilterLeavesTheArgumentsClean(): void
	{
		$state = PaginationState::fromArguments(null, null);

		self::assertSame([], $state->toArguments());
		self::assertSame([], $state->toArguments([]));
		self::assertSame([], $state->toArguments(null));
	}

	#[DataProvider('inactiveFilterProvider')]
	public function testInactiveFiltersAreDropped(mixed $filter): void
	{
		self::assertNull(PaginationState::sanitiseFilter($filter));
	}

	public static function inactiveFilterProvider(): array
	{
		return [
			'null' => [null],
			'string' => ['oops'],
			'empty array' => [[]],
			'empty values' => [[5 => '', 6 => null, 7 => '0']],
			'init only' => [['init' => '1']],
		];
	}

	public function testSanitiseFilterKeepsActiveFiltersAndDropsInit(): void
	{
		self::assertSame(
			[5 => '3', 8 => ['x']],
			PaginationState::sanitiseFilter(['init' => '1', 5 => '3', 6 => '', 8 => ['x']])
		);
	}
}
