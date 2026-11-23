<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Provider;

use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class MetaDescriptionProvider
{
	public function __construct(
		private readonly MetaTagManagerRegistry $metaTagManagerRegistry
	) {
	}

	public function setDescription(AbstractEntity $record): void
	{
		$description = $this->getDescription($record);

		if ($description === '') {
			return;
		}

		$this->metaTagManagerRegistry
			->getManagerForProperty('description')
			->addProperty('description', $description, [], true)
		;
	}

	private function getDescription(AbstractEntity $record): string
	{
		foreach (['seoDescription', 'abstract'] as $property) {
			if (!ObjectAccess::isPropertyGettable($record, $property)) {
				continue;
			}

			$description = trim(
				(string)preg_replace(
					'/\s+/',
					' ',
					strip_tags((string)ObjectAccess::getProperty($record, $property))
				)
			);

			if ($description !== '') {
				return $description;
			}
		}

		return '';
	}
}
