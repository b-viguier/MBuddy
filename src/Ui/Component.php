<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Bveing\MBuddy\Siglot\Emitter;
use Bveing\MBuddy\Siglot\Signal;

interface Component extends Emitter
{
    public function template(): Template;

    public function id(): Id;

    public function modified(): Signal;
}
