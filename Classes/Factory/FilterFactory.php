<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Factory;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Domain\Filter\FilterInterface;
use ReflectionClass;
use RuntimeException;
use Spatie\StructureDiscoverer\Discover;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class FilterFactory
{
	public function __construct(
		private FrontendInterface $cache
	) {
	}

	public function getFilters(): array
	{
		// Attempt to get cached filters
		if ($this->cache->has(__FUNCTION__)) {
			return $this->cache->get(__FUNCTION__);
		}

		// Check if the configuration has been hardcoded
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['ll_anthology']['filters'])) {
			return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['ll_anthology']['filters'];
		}

		// If there is nothing else, autodetect available filters
		$projectPath = Environment::getProjectPath();
		$filterClasses = Discover::in($projectPath)->withAttribute(AsAnthologyFilter::class)->get();

		$filters = array_combine(
			array_map(
				function ($filterClass) {
					$reflectionClass = new ReflectionClass($filterClass);

					foreach ($reflectionClass->getAttributes(AsAnthologyFilter::class) as $filterAttribute) {
						return $filterAttribute->newInstance()->filterType;
					}
				},
				$filterClasses
			),
			$filterClasses
		);

		$this->cache->set(__FUNCTION__, $filters);

		return $filters;
	}

	public function getConstraints(
		QueryResultInterface $filters,
		QueryInterface $query
	): array {
		$filterImplementations = $this->getFilters();
		$constraints = [];

		foreach ($filters as $filter) {
			if (!isset($filterImplementations[$filter->filterType])) {
				throw new NotImplementedException(
					sprintf(
						'Filter of type `%s` is not available',
						$filter->filterType
					),
					1759767112
				);
			}

			if (!is_subclass_of($filterImplementations[$filter->filterType], FilterInterface::class)) {
				throw new RuntimeException(
					sprintf(
						'`%s` is not a valid implementation of `%s`',
						$filterImplementations[$filter->filterType],
						FilterInterface::class
					),
					1760448468
				);
			}

			$constraints[] = $filterImplementations[$filter->filterType]::getConstraint(
				$filter,
				$query
			);
		}

		return array_filter($constraints);
	}
}
