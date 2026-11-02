<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Factory;

use LiquidLight\Anthology\Attribute\AsAnthologyRepository;
use LiquidLight\Anthology\Registry\AnthologyRepositoryRegistry;
use ReflectionClass;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class RepositoryFactory
{
	public function __construct(
		private readonly AnthologyRepositoryRegistry $repositoryRegistry
	) {
	}

	public function getRepositories(): array
	{
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['ll_anthology']['repositories'])) {
			trigger_error(
				'Manually specifying Anthology repositories has been removed, this configuration will be ignored',
				E_USER_DEPRECATED
			);
		}

		$repositoryClasses = $this->repositoryRegistry->get();

		return array_combine(
			$repositoryClasses,
			array_map(
				$this->getTcaName(...),
				$repositoryClasses
			),
		);
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
		return $this->getRepositoryProperty(
			repository: $repository,
			propertyName: 'tableName'
		);
	}

	public function getRepositoryLabel(string|Repository $repository): ?string
	{
		return $this->getRepositoryProperty(
			repository: $repository,
			propertyName: 'displayName'
		);
	}

	private function getRepositoryProperty(
		string|Repository $repository,
		string $propertyName
	): ?string {
		$repositoryReflection = new ReflectionClass($repository);
		$repositoryAttributes = $repositoryReflection->getAttributes(
			AsAnthologyRepository::class
		);

		if (count($repositoryAttributes) === 0) {
			return null;
		}

		return $repositoryAttributes[array_key_first($repositoryAttributes)]
			->newInstance()
			?->{$propertyName} ?? null
		;
	}
}
