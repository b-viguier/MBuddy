<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\Master;

use Bveing\MBuddy\Motif\Master\Id;
use PHPUnit\Framework\TestCase;

class IdTest extends TestCase
{
    public function testEditBuffer(): void
    {
        $masterId = Id::editBuffer();

        self::assertTrue($masterId->isEditBuffer());
        self::assertSame(-1, $masterId->toInt());
    }

    public function testRegularId(): void
    {
        $masterId = Id::fromInt(42);

        self::assertFalse($masterId->isEditBuffer());
        self::assertSame(42, $masterId->toInt());
    }

    public function testAll(): void
    {
        $masterIds = array_map(
            fn(Id $masterId) => $masterId->toInt(),
            iterator_to_array(Id::all()),
        );

        self::assertSame(range(0, 127), $masterIds);
    }
}
