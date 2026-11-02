<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Factory;

use LiquidLight\Anthology\Factory\RepositoryFactory;
use LiquidLight\Anthology\Registry\AnthologyRepositoryRegistry;
use LiquidLight\Anthology\Tests\Fixtures\Repository\NamedRepository;
use LiquidLight\Anthology\Tests\Fixtures\Repository\UnattributedRepository;
use LiquidLight\Anthology\Tests\Fixtures\Repository\UnnamedRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LiquidLight\Anthology\Factory\RepositoryFactory
 */
class RepositoryFactoryTest extends TestCase
{
	/**
	 * @covers \LiquidLight\Anthology\Factory\RepositoryFactory::getTcaName
	 */
	public function testGetTcaNameReturnsTableNameFromAttribute(): void
	{
		self::assertSame(
			'tx_fixture_table',
			$this->createSubject()->getTcaName(NamedRepository::class)
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Factory\RepositoryFactory::getRepositoryLabel
	 */
	public function testGetRepositoryLabelReturnsDisplayNameFromAttribute(): void
	{
		self::assertSame(
			'Fixture display name',
			$this->createSubject()->getRepositoryLabel(NamedRepository::class)
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Factory\RepositoryFactory::getRepositoryLabel
	 */
	public function testGetRepositoryLabelReturnsNullWhenDisplayNameIsNotSet(): void
	{
		self::assertNull(
			$this->createSubject()->getRepositoryLabel(UnnamedRepository::class)
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Factory\RepositoryFactory::getTcaName
	 */
	public function testGetTcaNameReturnsNullWhenClassHasNoAttribute(): void
	{
		self::assertNull(
			$this->createSubject()->getTcaName(UnattributedRepository::class)
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Factory\RepositoryFactory::getRepositories
	 */
	public function testGetRepositoriesMapsClassesToTableNames(): void
	{
		$subject = $this->createSubject([NamedRepository::class]);

		self::assertSame(
			[NamedRepository::class => 'tx_fixture_table'],
			$subject->getRepositories()
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Factory\RepositoryFactory::getRepositories
	 */
	public function testGetRepositoriesKeepsRepositoriesThatShareATable(): void
	{
		$subject = $this->createSubject([
			NamedRepository::class,
			UnnamedRepository::class,
		]);

		self::assertSame(
			[
				NamedRepository::class => 'tx_fixture_table',
				UnnamedRepository::class => 'tx_fixture_table',
			],
			$subject->getRepositories()
		);
	}

	private function createSubject(array $repositoryClasses = []): RepositoryFactory
	{
		$registry = $this->createMock(AnthologyRepositoryRegistry::class);
		$registry->method('get')->willReturn($repositoryClasses);

		return new RepositoryFactory($registry);
	}
}
