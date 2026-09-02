<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Unit\Events;

use LiquidLight\Anthology\Events\BeforeGetRecordsEvent;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

class BeforeGetRecordsEventTest extends TestCase
{
	public function testConstraintsMutationsPropagateBackToTheOriginalArray(): void
	{
		$constraints = ['existing'];

		$event = new BeforeGetRecordsEvent(
			$this->createMock(RepositoryInterface::class),
			$this->createMock(QueryInterface::class),
			$constraints,
			'logicalAnd',
			$this->createMock(ViewInterface::class),
			$this->createMock(RequestInterface::class),
			[]
		);

		$event->constraints[] = 'added';

		self::assertSame(['existing', 'added'], $event->constraints);
		self::assertSame(['existing', 'added'], $constraints);
	}
}
