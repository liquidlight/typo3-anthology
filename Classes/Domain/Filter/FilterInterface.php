<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Filter;

use LiquidLight\Anthology\Domain\Model\Filter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

interface FilterInterface
{
	public static function getLabel(): string;

	public static function getOptions(
		Filter $filter,
		array $pluginSettings
	): array;

	public static function getConstraint(
		Filter $filter,
		QueryInterface $queryInterface
	): ComparisonInterface|ConstraintInterface|null;
}
