<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Factory;

use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class RepositoryFactory
{
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
}
