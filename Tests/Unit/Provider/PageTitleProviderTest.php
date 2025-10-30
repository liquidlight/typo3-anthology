<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Provider;

use LiquidLight\Anthology\Provider\PageTitleProvider;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \LiquidLight\Anthology\Provider\PageTitleProvider
 */
class PageTitleProviderTest extends TestCase
{
	private PageTitleProvider $subject;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subject = new PageTitleProvider();

		// Reset global TCA
		$GLOBALS['TCA'] = [];
	}

	protected function tearDown(): void
	{
		unset($GLOBALS['TCA']);
		parent::tearDown();
	}

	public function testExtendsAbstractPageTitleProvider(): void
	{
		self::assertInstanceOf(AbstractPageTitleProvider::class, $this->subject);
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\PageTitleProvider::setTitle
	 */
	public function testSetTitleSetsEmptyTitleWhenNoLabelKeyConfigured(): void
	{
		$GLOBALS['TCA']['test_table'] = [];

		// Create a mock AbstractEntity
		$recordMock = $this->createMock(AbstractEntity::class);

		$this->subject->setTitle($recordMock, 'test_table');

		self::assertSame('', $this->subject->getTitle());
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\PageTitleProvider::setTitle
	 * @uses \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase
	 */
	public function testSetTitleUsesSimpleLabelWhenConfigured(): void
	{
		$GLOBALS['TCA']['test_table'] = [
			'ctrl' => [
				'label' => 'title',
			],
		];

		// Create a concrete test entity instead of using mock with dynamic properties
		$record = new class () extends AbstractEntity {
			public string $title = 'Test Title';

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->subject->setTitle($record, 'test_table');

		self::assertSame('Test Title', $this->subject->getTitle());
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\PageTitleProvider::setTitle
	 * @uses \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase
	 */
	public function testSetTitleHandlesNullPropertyValues(): void
	{
		$GLOBALS['TCA']['test_table'] = [
			'ctrl' => [
				'label' => 'title',
			],
		];

		// Create a concrete test entity with null title
		$record = new class () extends AbstractEntity {
			public ?string $title = null;

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->subject->setTitle($record, 'test_table');

		self::assertSame('', $this->subject->getTitle());
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\PageTitleProvider::setTitle
	 * @uses \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase
	 * @uses \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode
	 */
	public function testSetTitleCombinesLabelsWhenAlternativeLabelsForced(): void
	{
		$GLOBALS['TCA']['test_table'] = [
			'ctrl' => [
				'label' => 'title',
				'label_alt' => 'description',
				'label_alt_force' => true,
			],
		];

		// Create a concrete test entity
		$record = new class () extends AbstractEntity {
			public string $title = 'Test Title';

			public string $description = 'Test Description';

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->subject->setTitle($record, 'test_table');

		self::assertSame('Test Title Test Description', $this->subject->getTitle());
	}

	/**
	 * @covers \LiquidLight\Anthology\Provider\PageTitleProvider::setTitle
	 * @uses \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase
	 * @uses \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode
	 */
	public function testSetTitleConvertsUnderscoresToCamelCase(): void
	{
		$GLOBALS['TCA']['test_table'] = [
			'ctrl' => [
				'label' => 'main_title',
				'label_alt' => 'sub_title',
				'label_alt_force' => true,
			],
		];

		// Create a concrete test entity with camelCase properties
		$record = new class () extends AbstractEntity {
			public string $mainTitle = 'Main Title';

			public string $subTitle = 'Sub Title';

			public function __construct()
			{
				// Empty constructor to avoid parent constructor issues
			}
		};

		$this->subject->setTitle($record, 'test_table');

		self::assertSame('Main Title Sub Title', $this->subject->getTitle());
	}
}
