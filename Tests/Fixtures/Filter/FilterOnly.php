<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Fixtures\Filter;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;

#[AsAnthologyFilter('filter_only')]
class FilterOnly
{
	public static function getLabel(): string
	{
		return 'filter_only';
	}
}
