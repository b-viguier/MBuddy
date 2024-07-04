<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Support;

class SpyReceiver
{
    /** @var array<mixed[]> */
    public array $calls = [];

    public function slot(mixed ...$args): void
    {
        $this->calls[] = $args;
    }
}
