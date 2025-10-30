<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Domain\Query;

use LiquidLight\Anthology\Domain\Query\ConstraintBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LiquidLight\Anthology\Domain\Query\ConstraintBuilder
 */
class ConstraintBuilderTest extends TestCase
{
	private ConstraintBuilder $subject;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subject = new ConstraintBuilder();
	}

	public function testConstraintBuilderCanBeInstantiated(): void
	{
		self::assertInstanceOf(ConstraintBuilder::class, $this->subject);
	}

	public function testConstraintBuilderHasGetConstraintsMethod(): void
	{
		$reflection = new \ReflectionClass(ConstraintBuilder::class);
		self::assertTrue($reflection->hasMethod('getConstraints'));
	}

	public function testGetConstraintsMethodHasCorrectSignature(): void
	{
		$reflection = new \ReflectionClass(ConstraintBuilder::class);
		$method = $reflection->getMethod('getConstraints');

		$parameters = $method->getParameters();

		self::assertCount(3, $parameters);
		self::assertSame('filters', $parameters[0]->getName());
		self::assertSame('query', $parameters[1]->getName());
		self::assertSame('filterImplementations', $parameters[2]->getName());
	}

	public function testGetConstraintsMethodReturnsArray(): void
	{
		$reflection = new \ReflectionClass(ConstraintBuilder::class);
		$method = $reflection->getMethod('getConstraints');

		$returnType = $method->getReturnType();

		self::assertNotNull($returnType);
		self::assertSame('array', (string)$returnType);
	}
}
