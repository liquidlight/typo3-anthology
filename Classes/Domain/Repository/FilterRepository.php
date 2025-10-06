<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class FilterRepository extends Repository
{
	public function findByUids(array $uids): QueryResultInterface
	{
		// Set a value that will return nothing to prevent `in()` throwing an exception
		$uids[] = 0;

		$query = $this->createQuery();

		return $query
			->matching(
				$query->in('uid', $uids)
			)->execute()
		;
	}
}
