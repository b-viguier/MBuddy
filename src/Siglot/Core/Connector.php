<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot\Core;

class Connector
{
    public function __construct(private SignalMethod $signal, private SlotCollection $slots)
    {
    }

    public function chain(self $other): void
    {
        $this->slots->add(
            SlotMethod::fromWrappedSignal($other->signal, \Closure::fromCallable([$other->slots, 'invoke']))
        );
    }

    public function connect(SlotMethod $slot): void
    {
        $this->slots->add($slot);
    }
}
