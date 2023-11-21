<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Bveing\MBuddy\Ui\RemoteDom\Renderer;
use Bveing\MBuddy\Ui\Component\Internal\Id;
use Bveing\MBuddy\Ui\Websocket\Listener;

class Page implements RemoteDom\Updater, RemoteDom\Renderer, Listener
{
    private array $jsSenders = [];
    private array $jsUpdaters = [];

    private array $phpListeners = [];

    public function __construct(
        private string $title,
        private Websocket $websocket,
        private Component $body,
    ) {
        $this->websocket->setListener($this);
    }

    public function render(): string
    {
        $this->clearBuffers();

        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="translucent">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            
                <title>{$this->title}</title>
                
                <script>
                    var MBuddySocket = new WebSocket(
                        "ws://" + window.location.hostname + ":" + window.location.port + '{$this->websocket->getPath()}'
                    );
                    MBuddySocket.binaryType = "arraybuffer";
                
                    MBuddySocket.onopen = function() {
                        window.onerror = function(message, url, lineNumber) {
                            MBuddySocket.send(message + " at " + url + ":" + lineNumber);
                            return true;
                        };
                
                        console.log = function (msg) {
                            MBuddySocket.send(msg);
                        };
                        console.warn = function (msg) {
                            MBuddySocket.send(msg);
                        };
                        console.error = function (msg) {
                            MBuddySocket.send(msg);
                        };
                    }
                </script>
            </head>
            <body>
            {$this->body->render($this, $this)}
            
            <script>
                {$this->renderSenders()}
                {$this->renderUpdaters()}
            </script>

            </body>
            </html>
            HTML;
    }

    public function jsEventSender(
        Id $componentId,
        string $eventId,
        \Closure $onEvent,
        string $jsEventSerializer,
    ): Renderer {
        $this->jsSenders[] = func_get_args();

        return $this;
    }

    public function jsUpdater(Id $componentId, string $jsUpdater): Renderer
    {
        $this->jsUpdaters[] = func_get_args();

        return $this;
    }

    public function update(Id $componentId, string $value): RemoteDom\Updater
    {
        $this->websocket->send(json_encode([(string)$componentId, $value]));

        return $this;
    }

    public function onMessage(string $message): void
    {
        $data = json_decode($message, true);
        if (!is_array($data)) {
            return;
        }

        [$eventId, $componentId, $value] = $data;

        if (!isset($this->phpListeners[$componentId][$eventId])) {
            return;
        }

        $this->phpListeners[$componentId][$eventId]($value);
    }

    private function clearBuffers(): void
    {
        $this->jsSenders = [];
        $this->jsUpdaters = [];
        $this->phpListeners = [];
    }

    private function renderSenders(): string
    {
        $js = '';

        foreach ($this->jsSenders as [$componentId, $eventId, $onEvent, $jsEventSerializer]) {
            $this->phpListeners[(string)$componentId][$eventId] = $onEvent;

            $js .= <<<JS
                document.getElementById('{$componentId}').addEventListener('{$eventId}', function(event) {
                    MBuddySocket.send(JSON.stringify(['{$eventId}', '{$componentId}', ({$jsEventSerializer})(event)]));
                });
                JS;
        }

        return $js;
    }

    private function renderUpdaters(): string
    {
        $js = '';

        foreach ($this->jsUpdaters as [$componentId, $jsUpdater]) {
            $js .= <<<JS
                MBuddySocket.addEventListener('message', function(event) {
                    var msg = JSON.parse(event.data);
                    if(msg[0] !== '{$componentId}') {
                        return;
                    }
                    ({$jsUpdater})(
                        document.getElementById('{$componentId}'),
                        msg[1]
                    );
                });
                JS;
        }

        return $js;
    }


}