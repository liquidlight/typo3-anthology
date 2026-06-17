<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\ViewHelpers;

use TYPO3\CMS\Core\Domain\FlexFormFieldValues;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

class GetConfigurationViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('configurationPath', 'string', '', true);
		$this->registerArgument('flexformTransformed', 'array', 'The `pi_flexform_transformed` array for Typo3 v13', false);
		$this->registerArgument('flexform', FlexFormFieldValues::class, 'The `FlexFormFieldValues` object for Typo3 v14+', false);
	}

	public function render(): mixed
	{
		if (
			empty($this->arguments['flexformTransformed'])
			&& empty($this->arguments['flexform'])
		) {
			throw new Exception(
				'Either `FlexFormFieldValues` object or `pi_flexform_transformed` array must be supplied',
				1781261383
			);
		}

		$configurationPath = GeneralUtility::trimExplode('.', $this->arguments['configurationPath'], true);

		return isset($this->arguments['flexformTransformed'])
			? $this->arguments['flexformTransformed'][end($configurationPath)] ?? $this->arguments['flexformTransformed']['settings'][end($configurationPath)] ?? null
			: $this->getConfigurationFromFlexform($configurationPath) ?? null
		;
	}

	private function getConfigurationFromFlexform(array $configurationPath): mixed
	{
		if (
			(!$this->arguments['flexform'] instanceof FlexFormFieldValues)
			|| empty($configurationPath)
		) {
			return null;
		}

		$configuration = $this->arguments['flexform']?->getSheets() ?? [];

		foreach ($configurationPath as $configurationPathKey) {
			$configuration = $configuration[$configurationPathKey] ?? null;

			if (!$configuration) {
				break;
			}
		}

		return $configuration;
	}
}
