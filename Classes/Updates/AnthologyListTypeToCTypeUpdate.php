<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('anthologyListTypeToCTypeUpdate')]
final class AnthologyListTypeToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
	public function getTitle(): string
	{
		return 'Migrate Anthology plugins to content elements.';
	}

	public function getDescription(): string
	{
		return 'The Anthology plugin is now registered as a content element. Updates existing records and backend user permissions.';
	}

	protected function getListTypeToCTypeMapping(): array
	{
		return [
			'llanthology_anthologyview' => 'llanthology_anthologyview',
		];
	}
}
