<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Factory;

use LiquidLight\Anthology\Attribute\AsAnthologyPreFilter;
use LiquidLight\Anthology\Registry\AbstractAnthologyRegistry;
use LiquidLight\Anthology\Registry\AnthologyPreFilterRegistry;

class PreFilterFactory extends AbstractFilterFactory
{
	public function __construct(
		private readonly AnthologyPreFilterRegistry $preFilterRegistry
	) {
	}

	protected function getRegistry(): AbstractAnthologyRegistry
	{
		return $this->preFilterRegistry;
	}

	protected function getAttributeClass(): string
	{
		return AsAnthologyPreFilter::class;
	}
}
