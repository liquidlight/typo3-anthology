<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Provider;

use LiquidLight\Anthology\Provider\MetaDescriptionProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerInterface;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \LiquidLight\Anthology\Provider\MetaDescriptionProvider
 */
class MetaDescriptionProviderTest extends TestCase
{
	private MetaTagManagerRegistry&MockObject $registry;

	private MetaTagManagerInterface&MockObject $manager;

	private MetaDescriptionProvider $subject;

	protected function setUp(): void
	{
		parent::setUp();

		$this->manager = $this->createMock(MetaTagManagerInterface::class);
		$this->registry = $this->createMock(MetaTagManagerRegistry::class);
		$this->subject = new MetaDescriptionProvider($this->registry);
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\MetaDescriptionProvider::setDescription
	 */
	public function testSetDescriptionUsesSeoDescription(): void
	{
		$record = new class () extends AbstractEntity {
			public ?string $seoDescription = 'The SEO description';

			public ?string $abstract = 'The abstract';

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->expectDescription('The SEO description');

		$this->subject->setDescription($record);
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\MetaDescriptionProvider::setDescription
	 */
	public function testSetDescriptionFallsBackToAbstract(): void
	{
		$record = new class () extends AbstractEntity {
			public ?string $seoDescription = null;

			public ?string $abstract = 'The abstract';

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->expectDescription('The abstract');

		$this->subject->setDescription($record);
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\MetaDescriptionProvider::setDescription
	 */
	public function testSetDescriptionStripsTagsAndCollapsesWhitespace(): void
	{
		$record = new class () extends AbstractEntity {
			public ?string $seoDescription = "  <p>One\n\ntwo   <strong>three</strong></p>  ";

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->expectDescription('One two three');

		$this->subject->setDescription($record);
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\MetaDescriptionProvider::setDescription
	 */
	public function testSetDescriptionUsesAbstractWhenSeoDescriptionIsBlank(): void
	{
		$record = new class () extends AbstractEntity {
			public ?string $seoDescription = '   ';

			public ?string $abstract = 'The abstract';

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->expectDescription('The abstract');

		$this->subject->setDescription($record);
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\MetaDescriptionProvider::setDescription
	 */
	public function testSetDescriptionSkipsValuesThatCannotBeCastToString(): void
	{
		$record = new class () extends AbstractEntity {
			public array $seoDescription = ['not', 'a', 'string'];

			public ?string $abstract = 'The abstract';

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->expectDescription('The abstract');

		$this->subject->setDescription($record);
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\MetaDescriptionProvider::setDescription
	 */
	public function testSetDescriptionDoesNothingWhenNoDescriptionAvailable(): void
	{
		$record = new class () extends AbstractEntity {
			public ?string $seoDescription = null;

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->registry->expects(self::never())->method('getManagerForProperty');

		$this->subject->setDescription($record);
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\MetaDescriptionProvider::setDescription
	 */
	public function testSetDescriptionIgnoresRecordsWithoutDescriptionProperties(): void
	{
		$record = $this->createMock(AbstractEntity::class);

		$this->registry->expects(self::never())->method('getManagerForProperty');

		$this->subject->setDescription($record);
	}

	private function expectDescription(string $description): void
	{
		$this->registry
			->expects(self::once())
			->method('getManagerForProperty')
			->with('description')
			->willReturn($this->manager)
		;

		$this->manager
			->expects(self::once())
			->method('addProperty')
			->with('description', $description, [], true)
		;
	}
}
