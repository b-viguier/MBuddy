<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif;

use PHPUnit\Framework\TestCase;

class ProgramTest extends TestCase
{
    public function testGetValues(): void
    {
        $program = new \Bveing\MBuddy\Motif\Program(1, 2, 3);
        self::assertSame(1, $program->bankMsb());
        self::assertSame(2, $program->bankLsb());
        self::assertSame(3, $program->number());
    }
}
