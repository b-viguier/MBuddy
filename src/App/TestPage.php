<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App;

use Bveing\MBuddy\Ui\Page;
use Bveing\MBuddy\Ui\Component\Compound;
use Bveing\MBuddy\Ui\Component\Label;
use Bveing\MBuddy\Ui\Component\Button;
use Bveing\MBuddy\Ui\Websocket;
use Bveing\MBuddy\Ui\JsEventBus;
use Psr\Log\LoggerInterface;

class TestPage
{
    private Page $page;

    public function __construct(Websocket $websocket, LoggerInterface $logger)
    {
        $count = 0;
        $this->page = new Page(
            'MBuddy',
            new JsEventBus($websocket, $logger),
            new Compound(
                $label = new Label('Hello World'),
                new Button(
                    'Click me',
                    function() use($label, &$count) {
                        $label->setLabel('Clicked! ' . (++$count));
                    },
                ),
            ),
        );
    }

    public function render(): string
    {
        return $this->page->render();
    }
}