<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\RemoteDom;

class Label implements Component
{
    private Component\Internal\Id $id;
    private RemoteDom\Updater $domUpdater;

    public function __construct(
        private string $label,
    ) {
        $this->id = new Component\Internal\Id(self::class);
        $this->domUpdater = new RemoteDom\NullUpdater();
    }
    public function render(RemoteDom\Renderer $renderer, RemoteDom\Updater $updater): string
    {
        $renderer->jsUpdater(
            componentId: $this->id,
            jsUpdater: <<<JS
                function(element, value) {
                    element.innerHTML = value;
                }
                JS,
        );
        $this->domUpdater = $updater;

        return <<<HTML
            <span id="{$this->id}">{$this->label}</span>
            HTML;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        $this->domUpdater->update($this->id, $label);

        return $this;
    }
}