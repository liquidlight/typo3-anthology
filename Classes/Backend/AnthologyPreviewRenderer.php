<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Backend;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AnthologyPreviewRenderer
{
	public function __construct(
		private readonly ConnectionPool $connectionPool,
		private readonly PageRepository $pageRepository,
		private readonly FlexFormService $flexFormService
	) {
	}

	public function getPluginSettings(array $record): array
	{
		return $this->flexFormService->convertFlexFormContentToArray(
			$record['pi_flexform'] ?? ''
		)['settings'];
	}

	public function getModelData(array $record): array
	{
		global $TCA;

		$tcaName = $this->getPluginSettings($record)['tca'];

		return [
			'value' => $tcaName,
			'label' => $TCA[$tcaName]['ctrl']['title'],
			'icon' => $TCA[$tcaName]['ctrl']['iconfile'] ?? '',
		];
	}

	public function getSources(array $record): array
	{
		return array_map(
			fn ($sourceUid) => $this->pageRepository->getLanguageOverlay(
				'pages',
				$this->pageRepository->getRawRecord(
					'pages',
					$sourceUid
				)
			),
			GeneralUtility::intExplode(',', $record['pages'])
		);
	}

	public function getCorrespondingPage(array $record): ?array
	{
		$rawRecord = $this->pageRepository->getRawRecord(
			'pages',
			(int)match ($this->getPluginSettings($record)['mode']) {
				'list' => $this->getPluginSettings($record)['singleView'],
				'single' => $this->getPluginSettings($record)['listView'],
			}
		);

		return !!$rawRecord
			? $this->pageRepository->getLanguageOverlay('pages', $rawRecord)
			: null;
	}
}
