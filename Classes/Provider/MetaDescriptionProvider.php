<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Provider;

use Stringable;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class MetaDescriptionProvider
{
	/**
	 * Record properties to take the description from, in order of preference
	 */
	protected const DESCRIPTION_PROPERTIES = ['seoDescription', 'abstract'];

	public function __construct(
		protected readonly MetaTagManagerRegistry $metaTagManagerRegistry
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

	protected function getDescription(AbstractEntity $record): string
	{
		foreach (static::DESCRIPTION_PROPERTIES as $property) {
			if (!ObjectAccess::isPropertyGettable($record, $property)) {
				continue;
			}

			$value = ObjectAccess::getProperty($record, $property);

			if (!is_scalar($value) && !$value instanceof Stringable) {
				continue;
			}

			$description = trim(
				(string)preg_replace(
					'/\s+/',
					' ',
					strip_tags((string)$value)
				)
			);

			if ($description !== '') {
				return $description;
			}
		}

		return '';
	}
}
