<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Domain\Model;

use LiquidLight\Anthology\Domain\Model\Filter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LiquidLight\Anthology\Domain\Model\Filter
 */
class FilterTest extends TestCase
{
	private Filter $subject;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subject = new Filter();
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 */
	public function testGetParameterReturnsInitialValueForParameter(): void
	{
		self::assertNull($this->subject->getParameter());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 */
	public function testSetParameterSetsParameter(): void
	{
		$parameter = 'test value';
		$this->subject->setParameter($parameter);

		self::assertSame($parameter, $this->subject->getParameter());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 */
	public function testSetParameterAcceptsArrayParameter(): void
	{
		$parameter = ['key' => 'value'];
		$this->subject->setParameter($parameter);

		self::assertSame($parameter, $this->subject->getParameter());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::setParameter
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::getParameter
	 */
	public function testSetParameterAcceptsIntegerParameter(): void
	{
		$parameter = 123;
		$this->subject->setParameter($parameter);

		self::assertSame($parameter, $this->subject->getParameter());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::getOptions
	 */
	public function testGetOptionsReturnsInitialValueForOptions(): void
	{
		self::assertSame([], $this->subject->getOptions());
	}

	/**
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::setOptions
	 * @covers \LiquidLight\Anthology\Domain\Model\Filter::getOptions
	 */
	public function testSetOptionsSetsOptions(): void
	{
		$options = [
			['value' => '1', 'title' => 'Option 1'],
			['value' => '2', 'title' => 'Option 2'],
		];
		$this->subject->setOptions($options);

		self::assertSame($options, $this->subject->getOptions());
	}

	public function testHasPublicFilterTypeProperty(): void
	{
		$this->subject->filterType = 'search';
		self::assertSame('search', $this->subject->filterType);
	}

	public function testHasPublicTitleProperty(): void
	{
		$this->subject->title = 'Test Filter';
		self::assertSame('Test Filter', $this->subject->title);
	}

	public function testHasPublicSettingsProperty(): void
	{
		$this->subject->settings = '<T3FlexForms>...</T3FlexForms>';
		self::assertSame('<T3FlexForms>...</T3FlexForms>', $this->subject->settings);
	}
}
