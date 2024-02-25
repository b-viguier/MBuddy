<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

class Id implements \Stringable
{
    /** @var non-empty-string */
    private string $id;

    /**
     * @param non-empty-string $id
     */
    public function __construct(string $id)
    {
        $this->id = \preg_replace('/[^a-zA-Z0-9_-]/', '_', $id)
            ?? throw new \InvalidArgumentException('Invalid id');
        ;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
