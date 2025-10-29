<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Filter;

use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Model\Filter;

abstract class AbstractFilter implements FilterInterface
{
	public static function getLabel(): string
	{
		return static::LABEL;
	}

	public static function getOptions(
		Filter $filter,
		array $pluginSettings
	): array {
		return [];
	}
}
