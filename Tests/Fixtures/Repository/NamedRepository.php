<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Tests\Fixtures\Repository;

use LiquidLight\Anthology\Attribute\AsAnthologyRepository;

#[AsAnthologyRepository(tableName: 'tx_fixture_table', displayName: 'Fixture display name')]
class NamedRepository
{
}
