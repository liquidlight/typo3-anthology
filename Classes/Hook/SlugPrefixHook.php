<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Hook;

class SlugPrefixHook
{
	public static function getPrefix(&$params): string
	{
		return '';
	}
}
