<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot;

use Bveing\MBuddy\Siglot\Core\Connector;
use Bveing\MBuddy\Siglot\Core\SignalManager;
use Bveing\MBuddy\Siglot\Core\SlotMethod;

trait EmitterHelper
{
    private ?SignalManager $signalManager = null;

    public function getSignalConnector(SlotMethod $signal): Connector
    {
        $this->signalManager ??= new SignalManager();
        return $this->signalManager->getConnector($signal);
    }

    private function emit(Signal $signal): void
    {
        $this->signalManager?->emit($signal->method(), $signal->args());
    }
}