<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Provider;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;

class SimplePageTitleProvider extends AbstractPageTitleProvider
{
	public function setTitle(string $pageTitle): void
	{
		$this->title = $pageTitle;
	}
}
