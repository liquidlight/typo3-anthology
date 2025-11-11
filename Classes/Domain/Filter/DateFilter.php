<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Filter;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Domain\Filter\AbstractFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use LiquidLight\Anthology\Domain\Model\Filter;
use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

#[AsAnthologyFilter('llanthology_date')]
class DateFilter extends AbstractFilter implements FilterInterface
{
	protected const LABEL = 'LLL:EXT:ll_anthology/Resources/Private/Language/locallang_tca.xlf:tx_anthology_domain_model_filter.filter_type.date';

	private const RELATIVE_DATE_INTERVALS = [
		'24 hours',
		'7 days',
		'1 month',
		'3 months',
		'6 months',
		'1 year',
	];

	public static function getOptions(
		Filter $filter,
		array $pluginSettings
	): array {
		return match ($filter->getParsedSettings()['dateSpan']) {
			'relative' => self::getRelativeOptions($filter, $pluginSettings),
			default => self::getBoundOptions($filter, $pluginSettings)
		};
	}

	public static function getConstraint(
		Filter $filter,
		QueryInterface $query
	): ComparisonInterface|ConstraintInterface|null {
		return $filter->getParsedSettings()['dateSpan'] === 'relative'
			? self::getRelativeConstraint($filter, $query)
			: self::getBoundConstraint($filter, $query);
	}

	private static function getRelativeOptions(
		Filter $filter,
		array $pluginSettings
	): array {
		return array_map(
			function (string $interval): array {
				$dateInterval = DateInterval::createFromDateString($interval);
				$date = (new DateTime())->sub($dateInterval);

				return [
					'title' => $interval, // @todo Language handling
					'value' => $date->format('U'),
				];
			},
			self::RELATIVE_DATE_INTERVALS
		);
	}

	private static function getBoundOptions(
		Filter $filter,
		array $pluginSettings
	): array {
		$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
		$queryBuilder = $connectionPool->getQueryBuilderForTable($pluginSettings['tca']);

		$dateField = $filter->getParsedSettings()['dateField'];

		$queryBuilder
			->select($dateField)
			->from($pluginSettings['tca'])
			->orderBy($dateField, 'ASC')
		;

		$startDate = DateTime::createFromFormat('U', (string)$queryBuilder->executeQuery()->fetchOne());

		if (!$startDate) {
			return [];
		}

		$dateOptions = [];
		$dateOption = $startDate;

		$dateInterval = DateInterval::createFromDateString('1 ' . $filter->getParsedSettings()['dateSpan']);
		$dateFormat = match ($filter->getParsedSettings()['dateSpan']) {
			'months' => 'F Y',
			'years' => 'Y',
			default => throw new RuntimeException(
				'Invalid date span value supplied',
				1761742752
			)
		};

		do {
			$dateOptions[] = [
				'title' => $dateOption->format($dateFormat), // @todo Language handling
				'value' => $dateOption->format('U'),
			];

			$dateOption->add($dateInterval);
		} while ($dateOption <= new DateTimeImmutable());

		return array_reverse($dateOptions);
	}

	private static function getRelativeConstraint(
		Filter $filter,
		QueryInterface $query
	): ?ComparisonInterface {
		// @todo Handle future dates
		return !empty($filter->getParameter())
			? $query->greaterThanOrEqual(
				$filter->getParsedSettings()['dateField'],
				$filter->getParameter()
			)
			: null;
	}

	private static function getBoundConstraint(
		Filter $filter,
		QueryInterface $query
	): ?ConstraintInterface {
		// @todo Handle future dates

		$constraintStartDate = DateTime::createFromFormat('U', $filter->getParameter());

		if (!$constraintStartDate) {
			return null;
		}

		$constraintStartDate
			->setDate(
				(int)$constraintStartDate->format('Y'),
				$filter->getParsedSettings()['dateSpan'] === 'months'
					? (int)$constraintStartDate->format('m')
					: 1,
				1
			)
			->setTime(0, 0)
		;

		$constraintEndDate = clone $constraintStartDate;
		$constraintEndDate->add(
			DateInterval::createFromDateString(
				'1 ' . $filter->getParsedSettings()['dateSpan']
			)
		);

		return $query->logicalAnd(
			$query->greaterThanOrEqual(
				$filter->getParsedSettings()['dateField'],
				$constraintStartDate->format('U')
			),
			$query->lessThan(
				$filter->getParsedSettings()['dateField'],
				$constraintEndDate->format('U')
			)
		);
	}
}
