<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Events;

use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

class BeforeGetRecordsWithConstraintsEvent
{
	public function __construct(
		public RepositoryInterface &$repository,
		public QueryInterface &$query,
		public array &$constraints,
		public readonly string $constraintModeMethod,
		public ViewInterface &$view,
		public RequestInterface &$request
	) {
	}
}
