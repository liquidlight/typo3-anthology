<?php

declare(strict_types=1);

use LiquidLight\Anthology\Middleware\PageTitleMiddleware;

return [
	'frontend' => [
		'anthology-page-title' => [
			'target' => PageTitleMiddleware::class,
			'after' => [
				'typo3/cms-frontend/page-resolver',
			],
		],
	],
];
