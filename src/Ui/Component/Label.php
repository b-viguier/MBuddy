<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\RemoteDom;
use Bveing\MBuddy\Ui\JsEventBus;

class Label implements Component
{
    private const VALUE_EVENT = 'value';
    private Component\Internal\Id $id;
    private ?JsEventBus $jsEventBus = null;

    private const JS_SET_LABEL_FUNC = <<<JS
        function(value) {
            this.innerHTML = value;
        }
        JS;

    public function __construct(
        private string $label,
    ) {
        $this->id = new Component\Internal\Id(self::class);
    }
    public function render(JsEventBus $jsEventBus): string
    {
        $this->jsEventBus = $jsEventBus;

        return <<<HTML
            <span id="{$this->id}">{$this->label}</span>
            <script>
                {$jsEventBus->renderDownEventListener($this->id, self::VALUE_EVENT, self::JS_SET_LABEL_FUNC)}
            </script>
            HTML;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        $this->jsEventBus?->sendDownEvent($this->id, self::VALUE_EVENT, $label);

        return $this;
    }
}