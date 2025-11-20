<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Factory;

use LiquidLight\Anthology\Attribute\AsAnthologyRepository;
use ReflectionClass;
use RuntimeException;
use Spatie\StructureDiscoverer\Discover;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class RepositoryFactory
{
	public function __construct(
		private FrontendInterface $cache
	) {
	}

	public function getRepositories(): array
	{
		if ($this->cache->has(__FUNCTION__)) {
			return $this->cache->get(__FUNCTION__);
		}

		$projectPath = Environment::getProjectPath();
		$repositoryClasses = Discover::in($projectPath)->withAttribute(AsAnthologyRepository::class)->get();

		$repositories = array_combine(
			array_map(
				$this->getTcaName(...),
				$repositoryClasses
			),
			$repositoryClasses
		);

		$this->cache->set(__FUNCTION__, $repositories);

		return $repositories;
	}

	public function getRepository(string $repositoryClass): Repository
	{
		$repositoryClass = trim($repositoryClass, '\\');
		$repository = GeneralUtility::makeInstance($repositoryClass);

		if (!$repository instanceof Repository) {
			throw new RuntimeException(
				sprintf(
					'"%s" is not a valid instance of "%s"',
					$repository::class,
					Repository::class,
				),
				1758814328
			);
		}

		return $repository;
	}

	public function getTcaName(string|Repository $repository): ?string
	{
		$repositoryReflection = new ReflectionClass($repository);
		$repositoryAttributes = $repositoryReflection->getAttributes(
			AsAnthologyRepository::class
		);

		return $repositoryAttributes[array_key_first($repositoryAttributes)]
			?->newInstance()
			?->tableName
		;
	}
}
