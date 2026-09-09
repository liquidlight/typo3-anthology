<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Events;

use LiquidLight\Anthology\Events\BeforeAnthologyViewRenderEventInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

class BeforeAnthologyListViewRenderEvent implements BeforeAnthologyViewRenderEventInterface
{
	public function __construct(
		public readonly array $settings,
		public ViewInterface $view,
		public RequestInterface $request
	) {
	}
}
