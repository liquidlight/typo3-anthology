<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\EventListener;

use LiquidLight\Anthology\Backend\AnthologyPreviewRenderer;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Fluid\View\StandaloneView;

#[AsEventListener(
	identifier: 'llanthology/after-tca-compilation-event-listener',
	event: PageContentPreviewRenderingEvent::class
)]
class PreviewRenderingEventListener
{
	private const BACKEND_PREVIEW_PATH = 'EXT:ll_anthology/Resources/Private/Templates/Backend/ViewPreview.html';

	public function __construct(
		private readonly AnthologyPreviewRenderer $anthologyPreviewRenderer,
		private readonly StandaloneView $backendPreview
	) {
	}

	public function __invoke(PageContentPreviewRenderingEvent $event): void
	{
		if (
			$event->getTable() !== 'tt_content'
			|| $event->getRecordType() !== 'list' ||
			($event->getRecord()['list_type'] ?? false) !== 'llanthology_anthologyview'
		) {
			return;
		}

		$this->backendPreview->setTemplatePathAndFilename(self::BACKEND_PREVIEW_PATH);
		$this->backendPreview->assignMultiple([
			'settings' => $this->anthologyPreviewRenderer->getPluginSettings($event->getRecord()),
			'model' => $this->anthologyPreviewRenderer->getModelData($event->getRecord()),
			'sources' => $this->anthologyPreviewRenderer->getSources($event->getRecord()),
			'correspondingPage' => $this->anthologyPreviewRenderer->getCorrespondingPage($event->getRecord()),
		]);

		$event->setPreviewContent($this->backendPreview->render());
	}
}
