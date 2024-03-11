<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class Html implements Component
{
    use Trait\AutoId;
    use Trait\AutoVersion;

    public function __construct(
        private string $html,
    ) {
    }

    public function set(string $html): self
    {
        $this->html = $html;

        return $this->refresh();
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <span id="{{ id }}">
                {{ html }}
            </span>
            HTML,
            id: $this->id(),
            html: $this->html,
        );
    }
}
