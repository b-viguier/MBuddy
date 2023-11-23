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
        return <<<HTML
            <div class="w-100 h-100 p-3 mx-auto d-flex flex-column text-center">
              <div class="row flex-grow-1">
                <div class="col-sm align-self-center">
                  1
                </div>
                <div class="col-sm">
                  2
                </div>
                <div class="col-sm">
                  3
                </div>
              </div>
              <div class="row flex-grow-1">
                <div class="col-sm">
                  4
                </div>
                <div class="col-sm">
                  {$this->body->render()}
                </div>
                <div class="col-sm">
                  6
                </div>
              </div>
              <div class="row flex-grow-1">
                <div class="col-sm">
                  7
                </div>
                <div class="col-sm">
                  8
                </div>
                <div class="col-sm">
                  <i class="bi bi-sun-fill"></i>
                </div>
              </div>
            </div>
            HTML;
    }
}