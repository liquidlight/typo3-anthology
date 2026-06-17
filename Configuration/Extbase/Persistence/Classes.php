<?php

declare(strict_types=1);

use LiquidLight\Anthology\Domain\Model\Category;
use LiquidLight\Anthology\Domain\Model\Content;

return [
	Category::class => [
		'tableName' => 'sys_category',
	],
	Content::class => [
		'tableName' => 'tt_content',
	],
];
