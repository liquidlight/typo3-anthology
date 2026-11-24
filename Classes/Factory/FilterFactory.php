<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Factory;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Registry\AbstractAnthologyRegistry;
use LiquidLight\Anthology\Registry\AnthologyFilterRegistry;

class FilterFactory extends AbstractFilterFactory
{
	public function __construct(
		private readonly AnthologyFilterRegistry $filterRegistry
	) {
	}

	protected function getRegistry(): AbstractAnthologyRegistry
	{
		return $this->filterRegistry;
	}

	protected function getAttributeClass(): string
	{
		return AsAnthologyFilter::class;
	}
}
