<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\EventListener;

use LiquidLight\Anthology\Backend\AnthologyPreviewRenderer;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

#[AsEventListener(
	identifier: 'llanthology/after-tca-compilation-event-listener',
	event: PageContentPreviewRenderingEvent::class
)]
class PreviewRenderingEventListener
{
	protected const BACKEND_PREVIEW_PATH = 'EXT:ll_anthology/Resources/Private/Templates/Backend/ViewPreview.html';

	protected const PLUGIN_SIGNATURE = 'llanthology_anthologyview';

	public function __construct(
		protected readonly AnthologyPreviewRenderer $anthologyPreviewRenderer,
		protected readonly ViewFactoryInterface $viewFactory
	) {
	}

	public function __invoke(PageContentPreviewRenderingEvent $event): void
	{
		if (
			$event->getTable() !== 'tt_content'
			|| $event->getRecordType() !== 'list' ||
			($event->getRecord()['list_type'] ?? false) !== static::PLUGIN_SIGNATURE
		) {
			return;
		}

		$viewFactoryData = new ViewFactoryData(
			templatePathAndFilename: static::BACKEND_PREVIEW_PATH,
			request: $event->getPageLayoutContext()->getCurrentRequest(),
		);

		$view = $this->viewFactory->create($viewFactoryData);
		$view->assignMultiple([
			'settings' => $this->anthologyPreviewRenderer->getPluginSettings($event->getRecord()),
			'model' => $this->anthologyPreviewRenderer->getModelData($event->getRecord()),
			'sources' => $this->anthologyPreviewRenderer->getSources($event->getRecord()),
			'correspondingPage' => $this->anthologyPreviewRenderer->getCorrespondingPage($event->getRecord()),
		]);

		$event->setPreviewContent($view->render());
	}
}
