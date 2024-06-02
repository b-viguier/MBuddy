<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot\Core;

class Connection
{
    public function __construct(public SignalMethod $signal, public SlotCollection $slots)
    {
    }
}
