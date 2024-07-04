<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Support;

use Bveing\MBuddy\Siglot\Emitter;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Signal;

class SpyEmitter implements Emitter
{
    use EmitterHelper;

    public function signal(mixed ...$args): Signal
    {
        return Signal::auto();
    }

    public function doEmit(mixed ...$args): void
    {
        $this->emit($this->signal(...$args));
    }
}
