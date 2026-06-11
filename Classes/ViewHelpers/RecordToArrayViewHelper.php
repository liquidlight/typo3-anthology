<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class RecordToArrayViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('record', 'mixed', 'A TYPO3 Record domain object', true);
	}

	public function render(): array
	{
		if (is_array($this->arguments['record'])) {
			return $this->arguments['record'];
		}

		if (!method_exists($this->arguments['record'], 'toArray')) {
			throw new Exception(
				sprintf(
					'Argument "record" must be an array or object with a toArray() method, "%s" given',
					get_class($this->arguments['record'])
				),
				1781191532
			);
		}

		return $this->arguments['record']->toArray();
	}
}
