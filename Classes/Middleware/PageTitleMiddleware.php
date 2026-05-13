<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Middleware;

use LiquidLight\Anthology\Provider\PageTitleProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Stream;

class PageTitleMiddleware implements MiddlewareInterface
{
	private const PAGE_TITLE_PLACEHOLDER = '####ANTHOLOGY_RECORD_TITLE_PLACEHOLDER####';

	public function __construct(
		private PageTitleProvider $pageTitleProvider
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
				$this->getPageTitle($request)
			)
		);

		return $response->withBody($body);
	}

	private function getPageTitle(ServerRequestInterface $request): string
	{
		return $request->getAttribute('frontend.controller')?->config['pageTitleCache'][PageTitleProvider::class] ?? '';
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
