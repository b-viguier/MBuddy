<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Trait;

use Bveing\MBuddy\Ui\Id;

trait AutoId
{
    private ?Id $id;

    public function id(): Id
    {
        return $this->id ?? $this->id = new Id(
            uniqid(
                ($pos = strrpos(self::class, '\\')) !== false
                    ? substr(self::class, $pos + 1)
                    : self::class,
            ).'_',
        );
    }
}
