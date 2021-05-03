<?php

namespace bviguier\tests\MBuddy;

use bviguier\MBuddy\SongId;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use PHPUnit\Framework\TestCase;

class SongIdTest extends TestCase
{
    public function testCreation(): void
    {
        $songId = new SongId($id = 51);

        $this->assertSame($id, $songId->id());
    }

    public function testFirst(): void
    {
        $this->assertSame(1, SongId::first()->id());
    }

    public function testDefault(): void
    {
        $this->assertSame(SongId::DEFAULT_ID, SongId::default()->id());
    }

    /**
     * @dataProvider nextSongIdProvider
     */
    public function testNext(int $id, int $expectedNext): void
    {
        $this->assertSame($expectedNext, (new SongId($id))->next()->id());
    }

    /**
     * @return iterable<int,array{int,int}>
     */
    public function nextSongIdProvider(): iterable
    {
        yield [5, 6];
        yield [99, 99];
        yield [1, 2];
    }

    /**
     * @dataProvider previousSongIdProvider
     */
    public function testPrevious(int $id, int $expectedPrevious): void
    {
        $this->assertSame($expectedPrevious, (new SongId($id))->previous()->id());
    }

    /**
     * @return iterable<int,array{int,int}>
     */
    public function previousSongIdProvider(): iterable
    {
        yield [6, 5];
        yield [99, 98];
        yield [1, 0];
        yield [0, 0];
    }

    /**
     * @dataProvider stringSongIdProvider
     */
    public function testToString(SongId $songId, string $expected): void
    {
        $this->assertSame($expected, ($songId)->__toString());
    }

    /**
     * @return iterable<array{SongId,string}>
     */
    public function stringSongIdProvider(): iterable
    {
        yield [SongId::default(), '00'];
        yield [new SongId(9), '09'];
        yield [new SongId(51), '51'];
    }
}
