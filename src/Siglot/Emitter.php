<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot;

use Bveing\MBuddy\Siglot\Core\Connector;
use Bveing\MBuddy\Siglot\Core\SlotMethod;

interface Emitter
{
    public function getSignalConnector(SlotMethod $signal): Connector;
}