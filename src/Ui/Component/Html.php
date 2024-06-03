<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class Html implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;
    use EmitterHelper;

    public function __construct(
        private string $html,
    ) {
    }

    public function set(string $html): self
    {
        $this->html = $html;
        $this->refresh();

        return $this;
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
