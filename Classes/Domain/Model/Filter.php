<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Filter extends AbstractEntity
{
	public string $filterType = '';

	public string $title = '';

	public string $displayMode = '';

	public array $searchFields = [];

	public string $dateField = '';

	public string $dateSpan = '';

	public ?Category $category = null;
}
