<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\EventListener;

use LiquidLight\Anthology\Factory\FilterFactory;
use LiquidLight\Anthology\Factory\RepositoryFactory;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Cache\Event\CacheWarmupEvent;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

#[AsEventListener(
	identifier: 'llanthology/cache-warmup-event-listener',
	event: CacheWarmupEvent::class
)]
class CacheWarmupEventListener
{
	private const CACHE_GROUP = 'system';

	public function __construct(
		private readonly RepositoryFactory $repositoryFactory,
		private readonly FilterFactory $filterFactory,
		private readonly FrontendInterface $cache
	) {
	}

	public function __invoke(CacheWarmupEvent $event): void
	{
		if (!in_array(self::CACHE_GROUP, $event->getGroups())) {
			return;
		}

		$this->repositoryFactory->getRepositories();
		$this->filterFactory->getFilters();
	}
}
