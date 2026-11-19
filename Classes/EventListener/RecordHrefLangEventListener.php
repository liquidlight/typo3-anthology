<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent;

/**
 * TYPO3 builds hreflang tags from the page translation only, so a record shown
 * through language fallback would advertise languages it is not translated into
 *
 * This restricts the alternates to the languages the record actually exists in
 */
#[AsEventListener(after: 'typo3-seo/hreflangGenerator')]
class RecordHrefLangEventListener
{
	private ?string $tableName = null;

	private ?int $recordUid = null;

	public function __construct(
		private readonly ConnectionPool $connectionPool
	) {
	}

	public function __invoke(ModifyHrefLangTagsEvent $event): void
	{
		$site = $event->getRequest()->getAttribute('site');
		$recordLanguageIds = $this->getRecordLanguageIds();

		if (!$site instanceof Site || $recordLanguageIds === null) {
			return;
		}

		$recordHrefLangs = array_map(
			fn (SiteLanguage $language) => $language->getHreflang(),
			array_filter(
				$site->getLanguages(),
				fn (SiteLanguage $language) => in_array(
					$language->getLanguageId(),
					$recordLanguageIds,
					true
				)
			)
		);

		$event->setHrefLangs(
			$this->removeUntranslatedHrefLangs(
				$event->getHrefLangs(),
				$recordHrefLangs
			)
		);
	}

	public function setRecord(string $tableName, int $recordUid): void
	{
		$this->tableName = $tableName;
		$this->recordUid = $recordUid;
	}

	/**
	 * Queried directly as Extbase can't directly determine whether a translation exists
	 *
	 * @return ?int[] Language IDs the record exists in, null when there is nothing translatable to check
	 */
	protected function getRecordLanguageIds(): ?array
	{
		if ($this->tableName === null || $this->recordUid === null) {
			return null;
		}

		$languageField = $GLOBALS['TCA'][$this->tableName]['ctrl']['languageField'] ?? null;
		$parentField = $GLOBALS['TCA'][$this->tableName]['ctrl']['transOrigPointerField'] ?? null;

		if (!$languageField || !$parentField) {
			return null;
		}

		$queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);

		return array_map(
			intval(...),
			$queryBuilder
				->select($languageField)
				->from($this->tableName)
				->where($queryBuilder->expr()->or(
					$queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($this->recordUid, Connection::PARAM_INT)),
					$queryBuilder->expr()->eq($parentField, $queryBuilder->createNamedParameter($this->recordUid, Connection::PARAM_INT))
				))
				->executeQuery()
				->fetchFirstColumn()
		);
	}

	/**
	 * Keep only the alternates for the given `hreflang` values (and `x-default`),
	 * a single language needs no alternates at all
	 *
	 * @param array<string, string> $hrefLangs hreflang => URL
	 * @param string[] $recordHrefLangs
	 *
	 * @return array<string, string>
	 */
	private function removeUntranslatedHrefLangs(
		array $hrefLangs,
		array $recordHrefLangs
	): array {
		$hrefLangs = array_intersect_key(
			$hrefLangs,
			array_flip($recordHrefLangs) + ['x-default' => true]
		);

		return count(array_diff_key($hrefLangs, ['x-default' => true])) < 2
			? []
			: $hrefLangs;
	}
}
