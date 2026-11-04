<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\PageTitle\PageTitleProviderManager;

class PageTitleMiddleware implements MiddlewareInterface
{
	private const PAGE_TITLE_PLACEHOLDER = '####ANTHOLOGY_RECORD_TITLE_PLACEHOLDER####';

	public function __construct(
		private PageTitleProviderManager $pageTitleProviderManager
	) {
	}

	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	): ResponseInterface {
		$response = $handler->handle($request);
		$content = $response->getBody()->__toString();

		$body = new Stream('php://temp', 'rw');
		$body->write(
			$this->replacePageTitle(
				$content,
				$this->pageTitleProviderManager->getTitle($request)
			)
		);

		return $response->withBody($body);
	}

	private function replacePageTitle($content, $pageTitle): string
	{
		return str_replace(
			static::PAGE_TITLE_PLACEHOLDER,
			$pageTitle,
			$content
		);
	}
}
