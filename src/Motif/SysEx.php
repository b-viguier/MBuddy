<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class SysEx implements \Stringable
{
    private const MIN_FIXED_SIZE = 6;
    private const SYSEX_PREFIX = "\xF0\x43";
    private const SYSEX_SUFFIX = "\xF7";
    private const MODEL_ID = "\x7F\x03";


    private function __construct(private string $sysexMsg)
    {
    }

    public function __toString(): string
    {
        return $this->sysexMsg;
    }

    public static function fromBinaryString(string $binary): ?self
    {
        if (\strlen($binary) < self::MIN_FIXED_SIZE) {
            return null;
        }

        if (!\str_starts_with($binary, self::SYSEX_PREFIX) || !\str_ends_with($binary, self::SYSEX_SUFFIX)) {
            return null;
        }

        if (self::MODEL_ID !== \substr($binary, 3, 2)) {
            return null;
        }

        return new self($binary);
    }

    /**
     * @return list<int>
     */
    public function toBytes(): array
    {
        return \array_values(
            \unpack('C*', \substr($this->sysexMsg, 5, -1)) ?: throw new \RuntimeException('Cannot unpack SysEx'),
        );
    }

    public function deviceNumber(): int
    {
        return \ord($this->sysexMsg[2]);
    }

    public static function fromBytes(int $deviceNumber, string $data): self
    {
        \assert($deviceNumber >= 0x00 && $deviceNumber < 0xF0);
        \assert(
            \array_reduce(
                \unpack('C*', $data) ?: throw new \RuntimeException('Invalid binary data'),
                fn($carry, $byte) => $carry && $byte < 0xF0,
                true,
            ),
        );

        return new self(self::SYSEX_PREFIX.\chr($deviceNumber).self::MODEL_ID.$data.self::SYSEX_SUFFIX);
    }
}
