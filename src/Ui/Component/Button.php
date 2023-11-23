<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\RemoteDom;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\JsEventBus;

class Button implements Component
{
    private const CLICK_EVENT = 'click';

    private Component\Internal\Id $id;

    /**
     * @param \Closure(string $value):void $onClick
     */
    public function __construct(
        private string $label,
        \Closure $onClick,
        private JsEventBus $jsEventBus,
    ) {
        $this->id = new Component\Internal\Id(self::class);

        $this->jsEventBus->addUpEventListener($this->id, self::CLICK_EVENT, $onClick);
    }

    public function render(): string
    {
        return <<<HTML
            <button type="button" class="btn btn-primary" id="{$this->id}"
                onclick="{$this->jsEventBus->renderSendUpEvent($this->id, self::CLICK_EVENT, '')}"
                >
                {$this->label}
            </button>
            HTML;
    }
}