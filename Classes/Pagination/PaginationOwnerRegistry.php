<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Pagination;

/**
 * Decides which plugin on a page owns the route enhanced pagination (`/page-X`)
 *
 * Plugins render in page order, so the first paginated plugin to claim
 * ownership during a request is the first one on the page. Every other
 * paginated plugin paginates through the query string.
 */
class PaginationOwnerRegistry
{
	protected ?int $ownerUid = null;

	/**
	 * Claim ownership; only the first claim wins, repeat claims by the owner stay true
	 */
	public function claim(int $contentUid): bool
	{
		$this->ownerUid ??= $contentUid;

		return $this->ownerUid === $contentUid;
	}

	public function isOwner(int $contentUid): bool
	{
		return $this->ownerUid === $contentUid;
	}
}
