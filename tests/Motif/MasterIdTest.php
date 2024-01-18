<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif;

use PHPUnit\Framework\TestCase;
use Bveing\MBuddy\Motif\MasterId;

class MasterIdTest extends TestCase
{
    public function testEditBuffer(): void
    {
        $masterId = MasterId::editBuffer();

        self::assertTrue($masterId->isEditBuffer());
        self::assertSame(-1, $masterId->toInt());
    }

    public function testRegularId(): void
    {
        $masterId = MasterId::fromInt(42);

        self::assertFalse($masterId->isEditBuffer());
        self::assertSame(42, $masterId->toInt());
    }

    public function testAll(): void
    {
        $masterIds = array_map(
            fn(MasterId $masterId) => $masterId->toInt(),
            iterator_to_array(MasterId::getAll()),
        );

        self::assertSame(range(0, 127), $masterIds);
    }
}
