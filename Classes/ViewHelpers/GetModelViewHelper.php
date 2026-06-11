<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\ViewHelpers;

use LiquidLight\Anthology\Factory\RepositoryFactory;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetModelViewHelper extends AbstractViewHelper
{
	public function __construct(
		private readonly RepositoryFactory $repositoryFactory
	) {
	}

	public function initializeArguments(): void
	{
		$this->registerArgument('repository', 'string', 'An Anthology repository class name', true);
	}

	public function render(): array
	{
		global $TCA;

		$tcaName = $this->repositoryFactory->getTcaName($this->arguments['repository']);

		return [
			'value' => $tcaName,
			'label' => $TCA[$tcaName]['ctrl']['title'],
			'icon' => $TCA[$tcaName]['ctrl']['iconfile'] ?? '',
		];
	}
}
