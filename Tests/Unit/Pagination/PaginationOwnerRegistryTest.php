<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Pagination;

use LiquidLight\Anthology\Pagination\PaginationOwnerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LiquidLight\Anthology\Pagination\PaginationOwnerRegistry
 */
class PaginationOwnerRegistryTest extends TestCase
{
	public function testNobodyOwnsPaginationBeforeAClaim(): void
	{
		self::assertFalse((new PaginationOwnerRegistry())->isOwner(10));
	}

	public function testFirstClaimWins(): void
	{
		$registry = new PaginationOwnerRegistry();

		self::assertTrue($registry->claim(10));
		self::assertFalse($registry->claim(20));
		self::assertTrue($registry->isOwner(10));
		self::assertFalse($registry->isOwner(20));
	}

	public function testOwnerCanClaimRepeatedly(): void
	{
		$registry = new PaginationOwnerRegistry();

		self::assertTrue($registry->claim(10));
		self::assertTrue($registry->claim(10));
	}
}
