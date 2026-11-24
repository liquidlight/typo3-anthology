<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Fixtures\Filter;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Attribute\AsAnthologyPreFilter;

#[AsAnthologyFilter('both')]
#[AsAnthologyPreFilter('both')]
class Both
{
	public static function getLabel(): string
	{
		return 'both';
	}
}
