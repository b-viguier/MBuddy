<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

interface Component
{
    public function template(): Template;

    public function id(): Id;

    public function version(): int;
}
