<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Registry;

use LiquidLight\Anthology\Events\GetAnthologyRegistryEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAnthologyRegistry
{
	private array $registry = [];

	public function __construct(
		private readonly EventDispatcherInterface $eventDispatcher
	) {
	}

	public function add(?string $identifier): void
	{
		$this->registry[] = $identifier;
	}

	public function get(): array
	{
		$registry = array_unique(array_filter($this->registry));

		$this->eventDispatcher->dispatch(
			new GetAnthologyRegistryEvent(
				$registry,
				static::class
			)
		);

		return $registry;
	}
}
