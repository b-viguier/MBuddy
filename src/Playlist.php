<?php

namespace bviguier\MBuddy;

class Playlist
{
    static public function fromFile(string $filepath): self
    {
        $instance = new self();
        $instance->list = require $filepath;

        return $instance;
    }

    public function at(SongId $songId): void
    {
        $this->currentIndex = -1;
        foreach ($this->list as $index => $songDef) {
            if($songId->id() === $songDef[0]) {
                $this->currentIndex = $index;

                return;
            }
        }
    }

    public function current(): SongId
    {
        return new SongId($this->list[$this->currentIndex][0]);
    }

    public function next(): bool
    {
        if($this->currentIndex >= count($this->list) -1) {
            return false;
        }
        ++$this->currentIndex;

        return true;
    }

    public function previous(): bool
    {
        if($this->currentIndex === 0) {
            return false;
        }
        $this->currentIndex = ($this->currentIndex === -1 ? count($this->list) : $this->currentIndex) - 1;

        return true;
    }

    /** @var array<array{0:int,1:string}> */
    private array $list;
    private int $currentIndex = -1;

    private function __construct()
    {
    }
}
