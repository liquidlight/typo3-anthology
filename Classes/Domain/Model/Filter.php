<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Model;

use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Filter extends AbstractEntity
{
	public string $filterType = '';

	public string $title = '';

	public string $settings = '';

	protected mixed $parameter = null;

	protected array $options = [];

	protected array $parsedSettings = [];

	public function getParameter(): mixed
	{
		return $this->parameter;
	}

	public function setParameter(mixed $parameter): void
	{
		$this->parameter = $parameter;
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function setOptions(array $options): void
	{
		$this->options = $options;
	}

	public function getParsedSettings(): array
	{
		if (empty($this->parsedSettings)) {
			$this->parsedSettings = GeneralUtility::makeInstance(FlexFormService::class)
				->convertFlexFormContentToArray($this->settings)
			;
		}

		return $this->parsedSettings['settings'];
	}
}
