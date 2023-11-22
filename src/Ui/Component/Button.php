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
        private \Closure $onClick,
    ) {
        $this->id = new Component\Internal\Id(self::class);
    }

    public function render(JsEventBus $jsEventBus): string
    {
        $jsEventBus->addUpEventListener($this->id, self::CLICK_EVENT, $this->onClick);

        return <<<HTML
            <button id="{$this->id}"
                onclick="{$jsEventBus->renderSendUpEvent($this->id, self::CLICK_EVENT, '')}"
                >
                {$this->label}
            </button>
            HTML;
    }
}