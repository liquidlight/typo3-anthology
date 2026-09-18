<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Form\Container;

use TYPO3\CMS\Backend\Form\Container\InlineControlContainer as CoreInlineControlContainer;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

/**
 * Bootstrap's Collapse component resolves `data-bs-target` with an unescaped
 * `document.querySelector()` call, so it silently fails to expand/collapse IRRE
 * records embedded inside a FlexForm field, whose DOM id includes the Extbase
 * settings key verbatim (e.g. "settings.filters" contains a ".").
 *
 * @see https://github.com/twbs/bootstrap/issues/26516
 */
class InlineControlContainer extends CoreInlineControlContainer
{
	private const AFFECTED_TABLE = 'tx_anthology_domain_model_filter';

	public function render(): array
	{
		$result = parent::render();

		$foreignTable = $this->data['parameterArray']['fieldConf']['config']['foreign_table'] ?? null;
		if ($foreignTable === self::AFFECTED_TABLE) {
			$result['javaScriptModules'][] = JavaScriptModuleInstruction::create(
				'@liquidlight/ll-anthology/backend/fix-inline-collapse-toggle.js'
			);
		}

		return $result;
	}
}
