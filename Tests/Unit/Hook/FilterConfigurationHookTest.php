<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Hook;

use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\PreFilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use LiquidLight\Anthology\Hook\AbstractConfigurationHook;
use LiquidLight\Anthology\Hook\FilterConfigurationHook;
use LiquidLight\Anthology\Tests\Fixtures\Filter\Both;
use LiquidLight\Anthology\Tests\Fixtures\Filter\FilterOnly;
use LiquidLight\Anthology\Tests\Fixtures\Filter\PreFilterOnly;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\Tca\TcaMigration;
use TYPO3\CMS\Core\Configuration\Tca\TcaPreparation;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;

/**
 * @covers \LiquidLight\Anthology\Hook\FilterConfigurationHook
 * @uses \LiquidLight\Anthology\Hook\AbstractConfigurationHook
 */
class FilterConfigurationHookTest extends TestCase
{
	public function testExtendsAbstractConfigurationHook(): void
	{
		$reflection = new \ReflectionClass(FilterConfigurationHook::class);
		self::assertTrue($reflection->isSubclassOf(AbstractConfigurationHook::class));
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\FilterConfigurationHook::getSearchFields
	 */
	public function testGetSearchFieldsDelegatesWithSearchFieldTypes(): void
	{
		$recordedAllowedTypes = [];
		$subject = $this->createSubject($recordedAllowedTypes);
		$params = [];

		$subject->getSearchFields($params);

		self::assertSame(['input', 'text'], $recordedAllowedTypes);
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\FilterConfigurationHook::getDateFields
	 */
	public function testGetDateFieldsDelegatesWithDateFieldTypes(): void
	{
		$recordedAllowedTypes = [];
		$subject = $this->createSubject($recordedAllowedTypes);
		$params = [];

		$subject->getDateFields($params);

		self::assertSame(['datetime'], $recordedAllowedTypes);
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\FilterConfigurationHook::getAvailableFilters
	 */
	public function testGetAvailableFiltersUsesFilterFactoryByDefault(): void
	{
		$params = ['inlineParentFieldName' => 'settings.filters'];

		$this->createFilterSubject()->getAvailableFilters($params);

		self::assertSame(
			['both', 'filter_only'],
			array_column(array_slice($params['items'], 1), 'value')
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\FilterConfigurationHook::getAvailableFilters
	 */
	public function testGetAvailableFiltersUsesPreFilterFactoryForPreFilters(): void
	{
		$params = ['inlineParentFieldName' => 'settings.preFilters'];

		$this->createFilterSubject()->getAvailableFilters($params);

		self::assertSame(
			['both', 'pre_filter_only'],
			array_column(array_slice($params['items'], 1), 'value')
		);
	}

	private function createFilterSubject(): FilterConfigurationHook
	{
		$filterFactory = $this->createMock(FilterFactory::class);
		$filterFactory->method('getFilters')->willReturn([
			'filter_only' => FilterOnly::class,
			'both' => Both::class,
		]);

		$preFilterFactory = $this->createMock(PreFilterFactory::class);
		$preFilterFactory->method('getFilters')->willReturn([
			'both' => Both::class,
			'pre_filter_only' => PreFilterOnly::class,
		]);

		return new FilterConfigurationHook(
			$filterFactory,
			$preFilterFactory,
			$this->createMock(RepositoryFactory::class),
			$this->createFlexFormService(),
			$this->createMock(ConnectionPool::class)
		);
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

	private function createSubject(array &$recordedAllowedTypes): FilterConfigurationHook
	{
		return new class (
			$this->createMock(FilterFactory::class),
			$this->createMock(PreFilterFactory::class),
			$this->createMock(RepositoryFactory::class),
			$this->createFlexFormService(),
			$this->createMock(ConnectionPool::class),
			$recordedAllowedTypes
		) extends FilterConfigurationHook {
			public function __construct(
				FilterFactory $filterFactory,
				PreFilterFactory $preFilterFactory,
				RepositoryFactory $repositoryFactory,
				FlexFormService $flexFormService,
				ConnectionPool $connectionPool,
				private array &$recordedAllowedTypes
			) {
				parent::__construct($filterFactory, $preFilterFactory, $repositoryFactory, $flexFormService, $connectionPool);
			}

			public function getFields(array &$params, array $allowedTypes, ?int $anthologyPluginUid = null): void
			{
				$this->recordedAllowedTypes = $allowedTypes;
			}
		};
	}
}
