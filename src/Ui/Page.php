<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

class Page
{
    public function __construct(
        private string $title,
        private JsEventBus $jsEventBus,
        private Component $body,
    ) {
    }

    public function render(): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="translucent">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            
                <title>{$this->title}</title>
                
                {$this->jsEventBus->renderMain()}
            </head>
            <body>
            {$this->body->render($this->jsEventBus)}
            </body>
            </html>
            HTML;
    }
}