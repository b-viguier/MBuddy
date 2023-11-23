<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App;

use Bveing\MBuddy\Ui\Component\Compound;
use Bveing\MBuddy\Ui\Component\Label;
use Bveing\MBuddy\Ui\Component\Button;
use Bveing\MBuddy\Ui\JsEventBus;
use Bveing\MBuddy\Ui\Component;

class TestComponent implements Component
{
    private Compound $body;

    public function __construct(JsEventBus $jsEventBus)
    {
        $count = 0;
        $this->body = new Compound(
            $label = new Label('Hello World', $jsEventBus),
            new Button(
                'Click me',
                function () use ($label, &$count) {
                    $label->setLabel('Clicked! '.(++$count));
                },
                $jsEventBus,
            ),
        );
    }

    public function render(): string
    {
        return $this->body->render();
    }
}