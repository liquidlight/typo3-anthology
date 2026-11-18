<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Routing;

use LiquidLight\Anthology\Domain\Model\Category;
use LiquidLight\Anthology\Domain\Model\Filter;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class CategoryFilterUriBuilder extends UriBuilder
{
	public function buildCategoryFilterFrontendUri(
		int $pageUid,
		Filter $categoryFilter,
		Category $category,
		RequestInterface $request,
	): string {
		/** @var ExtbaseRequestParameters $extbase */
		$extbase = $request->getAttribute('extbase');

		$pluginNamespace = $this->extensionService->getPluginNamespace(
			$extbase->getControllerExtensionName(),
			$extbase->getPluginName(),
		);

		return $this->reset()
			->setRequest($request)
			->setTargetPageUid($pageUid)
			->setArguments([
				$pluginNamespace => [
					'filter' => [
						(int)$categoryFilter->getUid() => (int)$category->getUid(),
					],
				],
			])
			->buildFrontendUri()
		;
	}
}
