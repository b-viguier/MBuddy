<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

class Id implements \Stringable
{
    private string $id;

    public function __construct(string $class)
    {
        $this->id = uniqid(
            (
            ($pos = strrpos($class, '\\')) !== false
                ? substr($class, $pos + 1)
                : $class
            ).'_',
        );
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
