<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Template;

class Label implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;

    public function __construct(
        private string $label,
    ) {
    }

    public function template(): Template
    {
        return Template::create(
            <<<HTML
            <label id="{{ id }}">
                {{ label }}
            </label>
            HTML,
            id: $this->id(),
            label: $this->label,
        );
    }

    public function setText(string $label): self
    {
        $this->label = $label;
        $this->refresh();

        return $this;
    }
}
