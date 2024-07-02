<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core\Preset;

use Bveing\MBuddy\Motif\Master;

class Id
{
    public static function fromMasterId(Master\Id $masterId): self
    {
        return new self($masterId);
    }

    public static function fromInt(int $id): self
    {
        return new self(Master\Id::fromInt($id));
    }

    /**
     * @return \Traversable<self>
     */
    public static function all(): iterable
    {
        foreach (Master\Id::all() as $masterId) {
            yield self::fromMasterId($masterId);
        }
    }

    public function next(): ?self
    {
        return ($nextMasterId = $this->masterId->next()) === null ? null : self::fromMasterId($nextMasterId);
    }

    public function previous(): ?self
    {
        return ($previousMasterId = $this->masterId->previous()) === null ? null : self::fromMasterId($previousMasterId);
    }

    public function masterId(): Master\Id
    {
        return $this->masterId;
    }

    public function toInt(): int
    {
        return $this->masterId->toInt();
    }

    private function __construct(private Master\Id $masterId)
    {
        \assert(!$masterId->isEditBuffer());
    }
}
