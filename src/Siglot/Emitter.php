<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot;

use Bveing\MBuddy\Siglot\Core\Connector;
use Bveing\MBuddy\Siglot\Core\SignalMethod;

interface Emitter
{
    public function getConnector(SignalMethod $signal): Connector;
}
