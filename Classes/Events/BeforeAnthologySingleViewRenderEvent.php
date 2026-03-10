<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Events;

use LiquidLight\Anthology\Events\BeforeAnthologyViewRenderEventInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

class BeforeAnthologySingleViewRenderEvent implements BeforeAnthologyViewRenderEventInterface
{
	public function __construct(
		public AbstractEntity &$record,
		public ViewInterface &$view,
		public RequestInterface &$request
	) {
	}
}
