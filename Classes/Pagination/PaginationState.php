<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Pagination;

/**
 * Holds the current page (1-based) of every paginated plugin on a page
 *
 * The plugin that owns the route enhanced path (`/page-X`) is carried in the
 * `currentPage` argument; every other plugin is carried in the query string as
 * `pages[<contentUid>]`. Page 1 is the default and is never written.
 */
final class PaginationState
{
	public const OWNER_ARGUMENT = 'currentPage';

	public const OTHERS_ARGUMENT = 'pages';

	public const FILTER_ARGUMENT = 'filter';

	/**
	 * @param array<int, int> $others content uid => page, only pages above 1
	 */
	private function __construct(
		private readonly int $ownerPage,
		private readonly array $others,
	) {
	}

	/**
	 * Build from raw (untrusted) request arguments
	 */
	public static function fromArguments(mixed $ownerPage, mixed $others): self
	{
		$sanitisedOthers = [];

		if (is_array($others)) {
			foreach ($others as $contentUid => $page) {
				$contentUid = filter_var($contentUid, FILTER_VALIDATE_INT);
				$page = filter_var($page, FILTER_VALIDATE_INT);

				if ($contentUid !== false && $contentUid > 0 && $page !== false && $page > 1) {
					$sanitisedOthers[$contentUid] = $page;
				}
			}
		}

		$ownerPage = filter_var($ownerPage, FILTER_VALIDATE_INT);

		return new self(
			$ownerPage !== false && $ownerPage > 1 ? $ownerPage : 1,
			$sanitisedOthers,
		);
	}

	public function getPage(int $contentUid, bool $isOwner): int
	{
		return $isOwner
			? $this->ownerPage
			: ($this->others[$contentUid] ?? 1);
	}

	public function withPage(int $contentUid, bool $isOwner, int $page): self
	{
		$page = max(1, $page);

		if ($isOwner) {
			return new self($page, $this->others);
		}

		$others = $this->others;

		if ($page > 1) {
			$others[$contentUid] = $page;
		} else {
			unset($others[$contentUid]);
		}

		return new self($this->ownerPage, $others);
	}

	/**
	 * Reduce raw (untrusted) `filter` request arguments to the active ones,
	 * or null when none are set (mirrors the controller's active filter rule)
	 */
	public static function sanitiseFilter(mixed $filter): ?array
	{
		if (!is_array($filter)) {
			return null;
		}

		$filter = array_filter($filter);
		unset($filter['init']);

		return $filter ?: null;
	}

	/**
	 * Link arguments with the defaults (page 1, no filters) omitted
	 *
	 * @param array|null $filter active filters to keep on the link
	 * @return array<string, int|array>
	 */
	public function toArguments(?array $filter = null): array
	{
		$arguments = [];

		if ($this->ownerPage > 1) {
			$arguments[self::OWNER_ARGUMENT] = $this->ownerPage;
		}

		if ($this->others) {
			$others = $this->others;
			ksort($others);
			$arguments[self::OTHERS_ARGUMENT] = $others;
		}

		if ($filter) {
			$arguments[self::FILTER_ARGUMENT] = $filter;
		}

		return $arguments;
	}
}
