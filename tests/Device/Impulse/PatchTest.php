<?php

namespace bviguier\tests\MBuddy\Device\Impulse;

use bviguier\MBuddy\Device\Impulse\Patch;
use bviguier\MBuddy\Device\Impulse\PatchCorruption;
use bviguier\RtMidi\Message;
use PHPUnit\Framework\TestCase;

class PatchTest extends TestCase
{
    public const VALID_BYTES = [240, 0, 32, 41, 67, 0, 0, 53, 54, 108, 100, 79, 110, 50, 32, 1, 3, 2, 1, 64, 11, 1, 36, 55, 64, 4, 0, 56, 96, 64, 5, 0, 36, 84, 64, 16, 4, 36, 84, 64, 16, 4, 9, 91, 127, 0, 16, 8, 1, 9, 93, 127, 0, 16, 8, 1, 9, 71, 127, 0, 16, 8, 1, 9, 24, 127, 0, 16, 8, 1, 9, 25, 127, 0, 16, 8, 1, 9, 26, 127, 0, 16, 8, 1, 9, 27, 127, 0, 16, 8, 1, 9, 74, 127, 0, 16, 8, 1, 8, 58, 127, 0, 10, 8, 1, 8, 69, 127, 0, 16, 8, 1, 8, 71, 127, 0, 16, 8, 1, 8, 72, 127, 0, 16, 8, 1, 8, 60, 127, 0, 16, 8, 1, 8, 62, 127, 0, 16, 8, 1, 8, 64, 127, 0, 16, 8, 1, 8, 65, 127, 0, 16, 8, 1, 9, 7, 127, 0, 0, 8, 1, 9, 7, 127, 0, 1, 8, 1, 9, 7, 127, 0, 2, 8, 1, 9, 7, 127, 0, 3, 8, 1, 9, 7, 127, 0, 4, 8, 1, 9, 7, 127, 0, 5, 8, 1, 9, 47, 127, 0, 6, 8, 1, 9, 7, 127, 0, 7, 8, 1, 9, 7, 127, 0, 9, 8, 1, 17, 51, 127, 0, 16, 8, 1, 17, 52, 127, 0, 16, 8, 1, 17, 53, 127, 0, 16, 8, 1, 17, 54, 127, 0, 16, 8, 1, 17, 55, 127, 0, 16, 8, 1, 17, 56, 127, 0, 16, 8, 1, 17, 57, 127, 0, 16, 8, 1, 17, 58, 127, 0, 16, 8, 1, 17, 59, 127, 0, 16, 8, 1, 9, 1, 127, 0, 16, 8, 1, 247];

    static public function validSysex(): string
    {
        return join('', array_map('chr', self::VALID_BYTES));
    }

    public function testName(): void
    {
        $patch = Patch::fromBinString(self::validSysex());
        assert(null !== $patch);
        $patchCopy = $patch->withName('Name');

        $this->assertNotSame($patch->name(), $patchCopy->name());
        $this->assertSame('Name', $patchCopy->name());
    }

    public function testNameTooLong(): void
    {
        $patch = Patch::fromBinString(self::validSysex());
        assert(null !== $patch);
        $patchCopy = $patch->withName('123456ThisIsTooLong');

        $this->assertNotSame($patch->name(), $patchCopy->name());
        $this->assertSame('123456', $patchCopy->name());
    }

    public function testBinaryString(): void
    {
        $sysex = self::validSysex();
        $patch = Patch::fromBinString($sysex);
        assert(null !== $patch);

        $this->assertSame($sysex, $patch->toBinString());
        $this->assertSame($sysex, $patch->toSysexMessage()->toBinString());
    }

    public function testFromMessage(): void
    {
        $sysex = self::validSysex();
        $msg = Message::fromBinString($sysex);
        $patch = Patch::fromSysexMessage($msg);
        assert(null !== $patch);

        $this->assertSame($sysex, $patch->toBinString());
    }

    /**
     * @param array<int> $bytes
     * @dataProvider corruptedPatchProvider
     */
    public function testPatchCorruption(array $bytes, string $msg): void
    {
        $this->expectException(PatchCorruption::class);
        $this->expectExceptionMessage($msg);
        Patch::fromSysexMessage(Message::fromIntegers(...$bytes));
    }

    /**
     * @return iterable<string, array{0:array<int>, 1:string}>
     */
    public function corruptedPatchProvider(): iterable
    {
        $tooShortSysex = self::VALID_BYTES;
        array_splice($tooShortSysex, (int) (Patch::LENGTH / 2), 1);
        yield 'Too short' => [$tooShortSysex, 'Invalid Impulse Patch'];

        $ksort = function (array $a): array {
            ksort($a);

            return $a;
        };

        yield 'Missing sysex start' => [$ksort([0 => 0xFF] + self::VALID_BYTES), 'Invalid Sysex'];
        yield 'Missing sysex finish' => [$ksort([Patch::LENGTH - 1 => 0xFF] + self::VALID_BYTES), 'Invalid Sysex'];
        yield 'Novation ID Byte 1' => [$ksort([1 => 0xFF] + self::VALID_BYTES), 'Invalid Novation ID'];
        yield 'Novation ID Byte 2' => [$ksort([2 => 0xFF] + self::VALID_BYTES), 'Invalid Novation ID'];
        yield 'Novation ID Byte 3' => [$ksort([3 => 0xFF] + self::VALID_BYTES), 'Invalid Novation ID'];
        yield 'Impulse ID Byte 1' => [$ksort([4 => 0xFF] + self::VALID_BYTES), 'Invalid Impulse ID'];
        yield 'Impulse ID Byte 2' => [$ksort([5 => 0xFF] + self::VALID_BYTES), 'Invalid Impulse ID'];
        yield 'Impulse ID Byte 3' => [$ksort([6 => 0xFF] + self::VALID_BYTES), 'Invalid Impulse ID'];
    }

    public function testInvalidIdPattern(): void
    {
        $bytes = self::VALID_BYTES;
        $bytes[7] = ord('A');

        $this->assertNull(Patch::fromSysexMessage(Message::fromIntegers(...$bytes)));
    }
}
