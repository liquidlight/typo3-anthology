<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\EventListener;

use LiquidLight\Anthology\EventListener\RecordHrefLangEventListener;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent;

/**
 * @covers \LiquidLight\Anthology\EventListener\RecordHrefLangEventListener
 */
class RecordHrefLangEventListenerTest extends TestCase
{
	private const HREFLANGS = [
		'en' => 'https://example.com/en',
		'fr' => 'https://example.com/fr',
		'es' => 'https://example.com/es',
		'x-default' => 'https://example.com/en',
	];

	public function testRemovesLanguagesTheRecordIsNotTranslatedInto(): void
	{
		self::assertSame(
			[
				'en' => 'https://example.com/en',
				'fr' => 'https://example.com/fr',
				'x-default' => 'https://example.com/en',
			],
			$this->getHrefLangs([0, 1])
		);
	}

	public function testRemovesAllAlternatesWhenOnlyTheDefaultLanguageExists(): void
	{
		self::assertSame([], $this->getHrefLangs([0]));
	}

	public function testKeepsEverythingWhenTheRecordExistsInAllLanguages(): void
	{
		self::assertSame(self::HREFLANGS, $this->getHrefLangs([0, 1, 2]));
	}

	public function testLeavesAlternatesUntouchedWhenThereIsNothingTranslatableToCheck(): void
	{
		self::assertSame(self::HREFLANGS, $this->getHrefLangs(null));
	}

	/**
	 * @param ?int[] $recordLanguageIds
	 */
	private function getHrefLangs(?array $recordLanguageIds): array
	{
		$listener = new class ($this->createMock(ConnectionPool::class), $recordLanguageIds) extends RecordHrefLangEventListener {
			public function __construct(ConnectionPool $connectionPool, private readonly ?array $recordLanguageIds)
			{
				parent::__construct($connectionPool);
			}

			protected function getRecordLanguageIds(): ?array
			{
				return $this->recordLanguageIds;
			}
		};

		$event = new ModifyHrefLangTagsEvent(
			(new ServerRequest())->withAttribute('site', $this->createSite())
		);
		$event->setHrefLangs(self::HREFLANGS);

		$listener($event);

		return $event->getHrefLangs();
	}

	private function createSite(): Site
	{
		return new Site('test', 1, [
			'base' => 'https://example.com/',
			'languages' => [
				['languageId' => 0, 'locale' => 'en_GB.UTF-8', 'base' => '/', 'hreflang' => 'en'],
				['languageId' => 1, 'locale' => 'fr_FR.UTF-8', 'base' => '/fr/', 'hreflang' => 'fr'],
				['languageId' => 2, 'locale' => 'es_ES.UTF-8', 'base' => '/es/', 'hreflang' => 'es'],
			],
		]);
	}
}
