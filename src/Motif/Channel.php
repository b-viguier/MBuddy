<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class Channel implements \Stringable
{
    private function __construct(private int $channelId)
    {
    }

    public static function fromMidiByte(int $channelId): self
    {
        assert($channelId >= 0 && $channelId <= 15);

        return new self($channelId);
    }

    public function toMidiByte(): int
    {
        return $this->channelId;
    }

    public function toHumanReadable(): string
    {
        return strval($this->channelId + 1);
    }

    public function __toString(): string
    {
        return $this->toHumanReadable();
    }
}
