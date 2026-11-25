<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Events;

class GetAnthologyRegistryEvent
{
	public function __construct(
		public array &$registry,
		public readonly string $registryName
	) {
	}
}
