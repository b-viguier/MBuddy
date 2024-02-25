<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;

class Label implements Component
{
    use Trait\AutoId;
    use Trait\Childless;
    use Trait\Refreshable;

    public function __construct(
        private string $label,
    ) {
    }
    public function render(): string
    {
        $this->refreshNeeded = false;
        return <<<HTML
            <label id="{$this->id()}">{$this->label}</label>
            HTML;
    }

    public function setText(string $label): void
    {
        $this->label = $label;
        $this->refreshNeeded = true;
    }
}
