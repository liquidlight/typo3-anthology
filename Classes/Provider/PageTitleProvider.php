<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Provider;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class PageTitleProvider extends AbstractPageTitleProvider
{
	public function setTitle(AbstractEntity $record, string $tcaName): void
	{
		global $TCA;

		$labelKey = $TCA[$tcaName]['ctrl']['label'] ?? null;

		if (!$labelKey) {
			$this->title = '';
			return;
		}

		if (
			!($TCA[$tcaName]['ctrl']['label_alt'] ?? false)
			|| !($TCA[$tcaName]['ctrl']['label_alt_force'] ?? false)
		) {
			$this->title = (string)ObjectAccess::getProperty(
				$record,
				GeneralUtility::underscoredToLowerCamelCase($labelKey)
			);

			return;
		}

		$labelKeys = array_filter(
			GeneralUtility::trimExplode(
				',',
				$TCA[$tcaName]['ctrl']['label_alt'] ?? ''
			)
		);

		array_unshift($labelKeys, $labelKey);

		$titleComponents = array_map(
			fn ($labelKey) => (string)ObjectAccess::getProperty(
				$record,
				GeneralUtility::underscoredToLowerCamelCase($labelKey)
			),
			$labelKeys
		);

		$this->title = implode(' ', $titleComponents);
	}
}
