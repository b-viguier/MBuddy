<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\RemoteDom;
use Bveing\MBuddy\Ui\Component;

class Button implements Component
{
    private string $id;

    public function __construct(
        private string $label,
        private \Closure $onClick,
        string $id = null,
    ) {
        $this->id = $id ?? uniqid('button_');
    }

    public function render(RemoteDom\Renderer $renderer, RemoteDom\Updater $updater): string
    {
        $renderer->jsEventSender(
            componentId: $this->id,
            eventId: 'click',
            onEvent: fn(string $data) => ($this->onClick)(),
            jsEventSerializer: <<<JS
                function(event) {
                    return '';
                }
                JS,
        );

        return <<<HTML
            <button id="{$this->id}">{$this->label}</button>
            HTML;
    }
}