<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Registry;

use LiquidLight\Anthology\Events\GetAnthologyRegistryEvent;
use LiquidLight\Anthology\Registry\AnthologyFilterRegistry;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \LiquidLight\Anthology\Registry\AbstractAnthologyRegistry
 * @covers \LiquidLight\Anthology\Events\GetAnthologyRegistryEvent
 */
class AbstractAnthologyRegistryTest extends TestCase
{
	public function testGetReturnsUniqueNonEmptyEntries(): void
	{
		$subject = new AnthologyFilterRegistry($this->createMock(EventDispatcherInterface::class));
		$subject->add('A');
		$subject->add(null);
		$subject->add('B');
		$subject->add('A');

		self::assertSame(['A', 'B'], array_values($subject->get()));
	}

	public function testListenersCanRemoveAndReorderEntries(): void
	{
		$dispatcher = $this->createMock(EventDispatcherInterface::class);
		$dispatcher->method('dispatch')->willReturnCallback(
			static function (GetAnthologyRegistryEvent $event): GetAnthologyRegistryEvent {
				$event->registry = array_filter(
					$event->registry,
					static fn (string $class): bool => $class !== 'Unwanted'
				);
				usort(
					$event->registry,
					static fn (string $a, string $b): int => ($b === 'Priority') <=> ($a === 'Priority')
				);

				return $event;
			}
		);

		$subject = new AnthologyFilterRegistry($dispatcher);
		$subject->add('A');
		$subject->add('Unwanted');
		$subject->add('Priority');

		self::assertSame(['Priority', 'A'], $subject->get());
	}

	public function testEventCarriesConcreteRegistryName(): void
	{
		$dispatcher = $this->createMock(EventDispatcherInterface::class);
		$dispatcher->expects(self::once())->method('dispatch')->with(
			self::callback(
				static fn (GetAnthologyRegistryEvent $event): bool => $event->registryName === AnthologyFilterRegistry::class
			)
		);

		(new AnthologyFilterRegistry($dispatcher))->get();
	}
}
