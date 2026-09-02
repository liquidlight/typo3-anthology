<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Hook;

use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use LiquidLight\Anthology\Hook\AbstractConfigurationHook;
use LiquidLight\Anthology\Hook\PluginConfigurationHook;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\Tca\TcaMigration;
use TYPO3\CMS\Core\Configuration\Tca\TcaPreparation;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;

/**
 * @covers \LiquidLight\Anthology\Hook\PluginConfigurationHook
 * @uses \LiquidLight\Anthology\Hook\AbstractConfigurationHook
 */
class PluginConfigurationHookTest extends TestCase
{
	/**
	 * @covers \LiquidLight\Anthology\Hook\PluginConfigurationHook::getSortFields
	 */
	public function testGetSortFieldsPrependsDefaultOption(): void
	{
		$recordedCalls = [];
		$subject = $this->createSubject($recordedCalls);

		$params = [
			'items' => [],
			'row' => ['uid' => 42],
		];

		$subject->getSortFields($params);

		self::assertSame(
			[
				[
					'label' => 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_be.xlf:source.sortBy.default',
					'value' => null,
				],
				['label' => 'Title', 'value' => 'title'],
			],
			$params['items']
		);
	}

	/**
	 * @covers \LiquidLight\Anthology\Hook\PluginConfigurationHook::getSortFields
	 */
	public function testGetSortFieldsCallsGetFieldsWithAllTypesAndRowUid(): void
	{
		$recordedCalls = [];
		$subject = $this->createSubject($recordedCalls);

		$params = [
			'items' => [],
			'row' => ['uid' => 42],
		];

		$subject->getSortFields($params);

		self::assertSame(
			[
				[
					'allowedTypes' => [AbstractConfigurationHook::ALL_TYPES],
					'anthologyPluginUid' => 42,
				],
			],
			$recordedCalls
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

	private function createSubject(array &$recordedCalls): PluginConfigurationHook
	{
		return new class (
			$this->createMock(FilterFactory::class),
			$this->createMock(RepositoryFactory::class),
			$this->createFlexFormService(),
			$this->createMock(ConnectionPool::class),
			$recordedCalls
		) extends PluginConfigurationHook {
			public function __construct(
				FilterFactory $filterFactory,
				RepositoryFactory $repositoryFactory,
				FlexFormService $flexFormService,
				ConnectionPool $connectionPool,
				private array &$recordedCalls
			) {
				parent::__construct($filterFactory, $repositoryFactory, $flexFormService, $connectionPool);
			}

			public function getFields(array &$params, array $allowedTypes, ?int $anthologyPluginUid = null): void
			{
				$this->recordedCalls[] = [
					'allowedTypes' => $allowedTypes,
					'anthologyPluginUid' => $anthologyPluginUid,
				];

				$params['items'] = [
					['label' => 'Title', 'value' => 'title'],
				];
			}
		};
	}
}
