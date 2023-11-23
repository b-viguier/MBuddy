<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

class SinglePageApp
{
    public function __construct(
        private string $title,
        private Component $body,
        private JsEventBus $jsEventBus,
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
            
                <link rel="stylesheet" href="css/bootstrap.css" crossorigin="anonymous">
                <link rel="stylesheet" href="css/bootstrap-icons.css" crossorigin="anonymous">
                
                <style>
                    html,
                    body {
                      height: 100%;
                      background-color: #333;
                    }
                    
                    body {
                      display: -ms-flexbox;
                      display: flex;
                      color: #fff;
                      text-shadow: 0 .05rem .1rem rgba(0, 0, 0, .5);
                      box-shadow: inset 0 0 5rem rgba(0, 0, 0, .5);
                    }
                </style>

                <title>{$this->title}</title>
                
                {$this->jsEventBus->renderMain()}
            </head>
            <body>
                {$this->body->render()}
            </div>
            
            <script src="js/jquery.js" crossorigin="anonymous"></script>
            <script src="js/popper.js" crossorigin="anonymous"></script>
            <script src="js/bootstrap.js" crossorigin="anonymous"></script>

            </body>
            </html>
            HTML;
    }
}
