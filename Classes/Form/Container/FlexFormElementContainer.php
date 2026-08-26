<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Form\Container;

use TYPO3\CMS\Backend\Form\Container\FlexFormElementContainer as CoreFlexFormElementContainer;

class FlexFormElementContainer extends CoreFlexFormElementContainer
{
	private const PRE_FILTER_AWARE_TABLE = 'tx_anthology_domain_model_filter';

	public function render(): array
	{
		if (($this->data['tableName'] ?? null) !== static::PRE_FILTER_AWARE_TABLE) {
			return parent::render();
		}

		$parentField = $this->data['inlineParentFieldName'] ?? null;

		$this->data['flexFormDataStructureArray'] = array_filter(
			$this->data['flexFormDataStructureArray'],
			static function (array $flexFormFieldArray) use ($parentField): bool {
				$config = $flexFormFieldArray['config'] ?? [];

				if (isset($config['showWhenInlineParentField'])) {
					return $parentField === $config['showWhenInlineParentField'];
				}

				if (isset($config['hideWhenInlineParentField'])) {
					return $parentField !== $config['hideWhenInlineParentField'];
				}

				return true;
			}
		);

		return parent::render();
	}
}
