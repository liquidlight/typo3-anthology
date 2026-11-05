<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\ViewHelpers;

use LiquidLight\Anthology\Domain\Model\Category;
use LiquidLight\Anthology\Domain\Model\Filter;
use LiquidLight\Anthology\Domain\Repository\FilterRepository;
use LiquidLight\Anthology\Routing\CategoryFilterUriBuilder;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

final class CategoryLinkViewHelper extends AbstractTagBasedViewHelper
{
	protected const ACTIVE_CLASS = 'active';

	protected $tagName = 'a';

	public function __construct(
		private FilterRepository $filterRepository,
		private ExtensionService $extensionService,
		private CategoryFilterUriBuilder $uriBuilder,
	) {
		parent::__construct();
	}

	#[Override]
	public function initializeArguments(): void
	{
		parent::initializeArguments();

		$this
			->registerArgument(
				'listPage',
				'int',
				'The list page UID for the current model',
			)
			->registerArgument(
				'category',
				Category::class,
				'The category to be selected',
				true,
			)
		;
	}

	#[Override]
	public function render(): string
	{
		if (!($this->arguments['category'] ?? null) instanceof Category) {
			return (string)$this->renderChildren();
		}

		$category = $this->arguments['category'];
		$listPageUid = $this->getListPageUid();

		$categoryFilter = $this
			->filterRepository
			->findCategoryFilterByPage(
				$category,
				$listPageUid
			)
		;

		if (!$categoryFilter) {
			return (string)$this->renderChildren();
		}

		$filterUrl = $this->buildFilterUrl(
			$categoryFilter,
			$category,
			$listPageUid,
		);

		if (!$filterUrl) {
			return (string)$this->renderChildren();
		}

		$this->tag->addAttribute('href', $filterUrl);
		$this->setActiveState($categoryFilter, $category);
		$this->tag->setContent((string)$this->renderChildren());

		return $this->tag->render();
	}

	private function getListPageUid(): int
	{
		$listPageUid = (int)($this->arguments['listPage'] ?? 0);

		return $listPageUid > 0
			? $listPageUid
			: (
				$this
				->getExtbaseRequest()
				?->getAttribute('routing')
				?->getPageId() ?? 0
			);
	}

	private function buildFilterUrl(
		Filter $categoryFilter,
		Category $category,
		int $listPageUid
	): ?string {
		$request = $this->getExtbaseRequest();

		if (!$request) {
			return null;
		}

		return $this->uriBuilder
			->buildCategoryFilterFrontendUri(
				$listPageUid,
				$categoryFilter,
				$category,
				$request
			)
		;
	}

	private function getExtbaseRequest(): ?RequestInterface
	{
		if (!$this->renderingContext instanceof RenderingContext) {
			return null;
		}

		$request = $this->renderingContext->hasAttribute(ServerRequestInterface::class)
			? $this->renderingContext->getAttribute(ServerRequestInterface::class)
			: null;

		if (
			!$request instanceof ServerRequestInterface
			|| !$request->getAttribute('extbase') instanceof ExtbaseRequestParameters
		) {
			return null;
		}

		return $request instanceof RequestInterface
			? $request
			: new Request($request);
	}

	private function setActiveState(
		Filter $categoryFilter,
		Category $category
	): void {
		$arguments = $this->getExtbaseRequest()?->getAttribute('extbase')?->getArguments() ?? [];

		if ((int)($arguments['filter'][$categoryFilter->getUid()]) !== $category->getUid()) {
			return;
		}

		$classes = ($this->tag->getAttribute('class') ?? '') . ' ' . static::ACTIVE_CLASS;
		$this->tag->addAttribute('class', $classes);
	}
}
