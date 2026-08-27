<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Hook;

use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use LiquidLight\Anthology\Hook\AbstractConfigurationHook;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\Tca\TcaMigration;
use TYPO3\CMS\Core\Configuration\Tca\TcaPreparation;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;

/**
 * @covers \LiquidLight\Anthology\Hook\AbstractConfigurationHook
 */
class AbstractConfigurationHookTest extends TestCase
{
	public const TCA_TABLE = 'tx_test_table';

	protected function setUp(): void
	{
		parent::setUp();

		$GLOBALS['TCA'][self::TCA_TABLE]['columns'] = [
			'title' => [
				'label' => 'Title',
				'config' => ['type' => 'input'],
			],
			'description' => [
				'label' => 'Description',
				'config' => ['type' => 'text'],
			],
			'active' => [
				'label' => 'Active',
				'config' => ['type' => 'check'],
			],
			'category' => [
				'label' => 'Category',
				'config' => ['type' => 'select'],
			],
		];
	}

	protected function tearDown(): void
	{
		unset($GLOBALS['TCA'][self::TCA_TABLE]);
		parent::tearDown();
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\AbstractConfigurationHook::getFields
	 */
	public function testGetFieldsFiltersByAllowedTypes(): void
	{
		$subject = $this->createSubject();
		$params = [];

		$subject->getFields($params, ['input']);

		self::assertSame(
			[
				['label' => 'Title', 'value' => 'title'],
			],
			$params['items']
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\AbstractConfigurationHook::getFields
	 */
	public function testGetFieldsReturnsAllColumnsWhenAllTypesRequested(): void
	{
		$subject = $this->createSubject();
		$params = [];

		$subject->getFields($params, [AbstractConfigurationHook::ALL_TYPES]);

		self::assertSame(
			[
				['label' => 'Title', 'value' => 'title'],
				['label' => 'Description', 'value' => 'description'],
				['label' => 'Active', 'value' => 'active'],
				['label' => 'Category', 'value' => 'category'],
			],
			$params['items']
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\AbstractConfigurationHook::getFields
	 */
	public function testGetFieldsUsesProvidedAnthologyPluginUidWithoutCallingGetAnthologyPluginUid(): void
	{
		$subject = new class (
			$this->createMock(FilterFactory::class),
			$this->createMock(RepositoryFactory::class),
			$this->createFlexFormService(),
			$this->createMock(ConnectionPool::class)
		) extends AbstractConfigurationHook {
			protected function getAnthologyPluginUid(array $params): int
			{
				throw new \RuntimeException('getAnthologyPluginUid should not be called when a UID is provided');
			}

			protected function getAnthologyPluginTca(int $anthologyPluginUid): ?string
			{
				return AbstractConfigurationHookTest::TCA_TABLE;
			}
		};

		$params = [];

		$subject->getFields($params, [AbstractConfigurationHook::ALL_TYPES], 42);

		self::assertCount(4, $params['items']);
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\AbstractConfigurationHook::getFields
	 */
	public function testGetFieldsReturnsEarlyWhenNoAnthologyPluginUid(): void
	{
		$subject = $this->createSubject(anthologyPluginUid: 0);
		$params = ['items' => 'unchanged'];

		$subject->getFields($params, [AbstractConfigurationHook::ALL_TYPES]);

		self::assertSame('unchanged', $params['items']);
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\AbstractConfigurationHook::getFields
	 */
	public function testGetFieldsReturnsEarlyWhenTcaTableUnknown(): void
	{
		$subject = $this->createSubject(anthologyPluginUid: 1, tcaTableName: 'tx_unknown_table');
		$params = ['items' => 'unchanged'];

		$subject->getFields($params, [AbstractConfigurationHook::ALL_TYPES]);

		self::assertSame('unchanged', $params['items']);
	}

	private function createFlexFormService(): FlexFormService
	{
		// FlexFormService is a readonly class alias, which PHPUnit's mock
		// generator cannot double; a real instance is used instead.
		return new FlexFormService(
			$this->createMock(EventDispatcherInterface::class),
			new TcaMigration(),
			new TcaPreparation()
		);
	}

	private function createSubject(?int $anthologyPluginUid = 1, ?string $tcaTableName = self::TCA_TABLE): AbstractConfigurationHook
	{
		return new class (
			$this->createMock(FilterFactory::class),
			$this->createMock(RepositoryFactory::class),
			$this->createFlexFormService(),
			$this->createMock(ConnectionPool::class),
			$anthologyPluginUid,
			$tcaTableName
		) extends AbstractConfigurationHook {
			public function __construct(
				FilterFactory $filterFactory,
				RepositoryFactory $repositoryFactory,
				FlexFormService $flexFormService,
				ConnectionPool $connectionPool,
				private readonly ?int $stubAnthologyPluginUid,
				private readonly ?string $stubTcaTableName
			) {
				parent::__construct($filterFactory, $repositoryFactory, $flexFormService, $connectionPool);
			}

			protected function getAnthologyPluginUid(array $params): int
			{
				return $this->stubAnthologyPluginUid ?? 0;
			}

			protected function getAnthologyPluginTca(int $anthologyPluginUid): ?string
			{
				return $this->stubTcaTableName;
			}
		};
	}
}
