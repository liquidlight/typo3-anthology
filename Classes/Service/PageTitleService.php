<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Service;

use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageTitleService
{
	private const PAGE_TITLE_PLACEHOLDER = '####ANTHOLOGY_RECORD_TITLE_PLACEHOLDER####';

	public function replacePageTitle(string $pageContent): string
	{
		$registry = GeneralUtility::makeInstance(Registry::class);

		return str_replace(
			self::PAGE_TITLE_PLACEHOLDER,
			$registry->get('ll_anthology', 'record_page_title'),
			$pageContent
		);
	}
}
