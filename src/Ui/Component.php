<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Bveing\MBuddy\Core\Signal;

interface Component
{
    public function template(): Template;

    public function id(): Id;

    public function modified(): Signal\Signal0;
}
