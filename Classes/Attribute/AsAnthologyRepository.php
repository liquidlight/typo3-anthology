<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsAnthologyRepository
{
	public function __construct(
		public readonly string $tableName,
		public readonly ?string $displayName = null
	) {
	}
}
