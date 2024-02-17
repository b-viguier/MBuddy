<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

class Id implements \Stringable
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = preg_replace('/[^a-zA-Z0-9_-]/', '_', $id);
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
