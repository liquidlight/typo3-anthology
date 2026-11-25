<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Fixtures\Filter;

use LiquidLight\Anthology\Attribute\AsAnthologyPreFilter;

#[AsAnthologyPreFilter('pre_filter_only')]
class PreFilterOnly
{
	public static function getLabel(): string
	{
		return 'pre_filter_only';
	}
}
