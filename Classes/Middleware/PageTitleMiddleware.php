<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Middleware;

use LiquidLight\Anthology\Service\PageTitleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Stream;

class PageTitleMiddleware implements MiddlewareInterface
{
	public function __construct(
		private PageTitleService $pageTitleService
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
			$this->pageTitleService->replacePageTitle($content)
		);

		return $response->withBody($body);
	}
}
