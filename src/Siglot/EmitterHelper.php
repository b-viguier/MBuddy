<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot;

use Bveing\MBuddy\Siglot\Core\Connector;
use Bveing\MBuddy\Siglot\Core\SignalMethod;
use Bveing\MBuddy\Siglot\Core\SignalRouter;

trait EmitterHelper
{
    public function getConnector(SignalMethod $signal): Connector
    {
        $this->signalManager ??= new SignalRouter();

        return $this->signalManager->getConnector($signal);
    }
    private ?SignalRouter $signalManager = null;

    private function emit(Signal $signal): void
    {
        $this->signalManager?->emit($signal);
    }
}
