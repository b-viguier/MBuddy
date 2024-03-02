<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;

class Script implements Component
{
    use Trait\AutoId;
    use Trait\Childless;
    use Trait\Refreshable;


    public function render(): string
    {
        $this->refreshNeeded = false;
        return <<<HTML
            <script id="{$this->id()}">
                {$this->script}
            </script>
            HTML;
    }

    public function exec(string $script): void
    {
        $this->script = $script;
        $this->refreshNeeded = true;
    }

    private string $script = '';
}
