<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ContentRepository extends Repository
{
	protected $defaultOrderings = [
		'sorting' => QueryInterface::ORDER_ASCENDING,
	];
}
