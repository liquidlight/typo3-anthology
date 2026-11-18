<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\ViewHelpers;

use LiquidLight\Anthology\Pagination\PaginationOwnerRegistry;
use LiquidLight\Anthology\Pagination\PaginationState;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the link arguments for a page of the current plugin's pagination,
 * keeping the pages of any other plugins on the same page and the active filters.
 *
 * <f:link.action arguments="{a:paginationArguments(page: 2)}">2</f:link.action>
 */
final class PaginationArgumentsViewHelper extends AbstractViewHelper
{
	public function __construct(
		private PaginationOwnerRegistry $ownerRegistry,
	) {
	}

	#[Override]
	public function initializeArguments(): void
	{
		$this->registerArgument('page', 'int', 'The page to link to', true);
	}

	#[Override]
	public function render(): array
	{
		$request = $this->renderingContext->hasAttribute(ServerRequestInterface::class)
			? $this->renderingContext->getAttribute(ServerRequestInterface::class)
			: null;

		if (
			!$request instanceof ServerRequestInterface
			|| !$request->getAttribute('extbase') instanceof ExtbaseRequestParameters
		) {
			return $this->getFallbackArguments();
		}

		$arguments = $request->getAttribute('extbase')->getArguments();
		$contentUid = (int)($request->getAttribute('currentContentObject')?->data['uid'] ?? 0);

		return PaginationState::fromArguments(
			$arguments[PaginationState::OWNER_ARGUMENT] ?? null,
			$arguments[PaginationState::OTHERS_ARGUMENT] ?? null,
		)
			->withPage($contentUid, $this->ownerRegistry->isOwner($contentUid), (int)$this->arguments['page'])
			->toArguments(PaginationState::sanitiseFilter($arguments[PaginationState::FILTER_ARGUMENT] ?? null))
		;
	}

	/**
	 * Without a plugin request the only sensible link is the owner's
	 */
	private function getFallbackArguments(): array
	{
		return PaginationState::fromArguments((int)$this->arguments['page'], null)->toArguments();
	}
}
