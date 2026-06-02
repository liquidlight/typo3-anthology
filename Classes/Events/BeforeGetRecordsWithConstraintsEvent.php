<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Events;

use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 * @deprecated Use BeforeGetRecordsEvent instead
 */
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
		trigger_error(
			self::class . ' is deprecated. Use BeforeGetRecordsEvent instead.',
			E_USER_DEPRECATED
		);
	}
}
