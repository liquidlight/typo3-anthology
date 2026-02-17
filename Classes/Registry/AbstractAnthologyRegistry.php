<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Registry;

abstract class AbstractAnthologyRegistry
{
	private array $registry = [];

	public function add(?string $identifier): void
	{
		$this->registry[] = $identifier;
	}

	public function get(): array
	{
		return array_unique(array_filter($this->registry));
	}
}
