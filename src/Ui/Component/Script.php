<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class Script implements Component
{
    use Trait\AutoId;
    use Trait\AutoVersion;


    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <script id="{{ id }}">
                {{ script }}
            </script>
            HTML,
            id: $this->id(),
            script: $this->script,
        );
    }

    public function exec(string $script): void
    {
        $this->refresh();
        $this->script = $script;
    }

    private string $script = '';
}
