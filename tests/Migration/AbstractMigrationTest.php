<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\AbstractMigration;
use PHPUnit\Framework\TestCase;

class AbstractMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = $this->getMockForAbstractClass(AbstractMigration::class);
    }

    public function testMigrate(): void
    {
        $this->assertFalse($this->migration->assert());
    }
}
