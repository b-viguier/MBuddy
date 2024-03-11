<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Trait;

trait AutoVersion
{
    public function version(): int
    {
        return $this->version;
    }

    private int $version = 0;

    private function refresh(): self
    {
        ++$this->version;

        return $this;
    }
}
