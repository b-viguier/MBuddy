<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;

class Html implements Component
{
    use Trait\AutoId;
    use Trait\Childless;
    use Trait\Refreshable;

    public function __construct(
        private string $html,
    ) {
    }

    public function set(string $html): self
    {
        $this->html = $html;
        $this->refreshNeeded = true;

        return $this;
    }

    public function render(): string
    {
        $this->refreshNeeded = false;
        return <<<HTML
            <span id="{$this->id()}">
                {$this->html}
            </span>
            HTML;
    }
}
