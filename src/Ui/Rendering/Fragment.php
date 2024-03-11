<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Rendering;

use Bveing\MBuddy\Ui\Id;

class Fragment
{
    public function __construct(
        private Id $id,
        private string $content,
    ) {
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function content(): string
    {
        return $this->content;
    }
}
