<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif;

use PHPUnit\Framework\TestCase;

class ProgramTest extends TestCase
{
    public function testGetValues(): void
    {
        $program = new \Bveing\MBuddy\Motif\Program(1, 2, 3);
        $this->assertSame(1, $program->bankMsb());
        $this->assertSame(2, $program->bankLsb());
        $this->assertSame(3, $program->number());
    }
}
