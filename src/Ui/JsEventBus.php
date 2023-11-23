<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Bveing\MBuddy\Ui\Component\Internal\Id;
use Psr\Log\LoggerInterface;

class JsEventBus implements Websocket\Listener
{
    private array $phpListeners;

    public function __construct(
        private Websocket $websocket,
        private LoggerInterface $logger,
    ) {
        $this->websocket->setListener($this);
        $this->phpListeners = [
            'console' => [
                'log' => fn(string $msg) => $this->logger->info("[Console] $msg"),
                'warn' => fn(string $msg) => $this->logger->warning("[Console] $msg"),
                'error' => fn(string $msg) => $this->logger->error("[Console] $msg"),
                'critical' => fn(string $msg) => $this->logger->critical("[Console] $msg"),
            ],
        ];
    }

    public function renderMain(): string
    {
        return <<<HTML
            <script>
                var MBuddySocket = new WebSocket(
                    "ws://" + window.location.hostname + ":" + window.location.port + '{$this->websocket->getPath()}'
                );
                MBuddySocket.binaryType = "arraybuffer";
            
                var MBuddyListeners = {};
                MBuddySocket.onopen = function() {
                    window.onerror = function(message, url, lineNumber) {
                        MBuddySocket.send(JSON.stringify(['console', 'critical', message + " at " + url + ":" + lineNumber]));
                        return true;
                    };
            
                    console.log = function (msg) {
                        MBuddySocket.send(JSON.stringify(['console', 'log', msg]));
                    };
                    console.warn = function (msg) {
                        MBuddySocket.send(JSON.stringify(['console', 'warn', msg]));
                    };
                    console.error = function (msg) {
                        MBuddySocket.send(JSON.stringify(['console', 'error', msg]));
                    };
                    
                    MBuddySocket.addEventListener('message', function(event) {
                        var msg = JSON.parse(event.data);
                        var listener = MBuddyListeners[msg[0]] ? MBuddyListeners[msg[0]][msg[1]] : undefined;
                        if(listener === undefined) {
                            return;
                        }
                        listener.bind(document.getElementById(msg[0]))(msg[2]);
                    });
                }
            </script>
            HTML;
    }

    public function renderSendUpEvent(Id $componentId, string $eventId, string $jsValue): string
    {
        return <<<JS
                (function(event) {
                    MBuddySocket.send(JSON.stringify(['{$componentId}', '{$eventId}', '{$jsValue}' ]));
                })()
            JS;
    }

    /**
     * @param \Closure(string $value):void $onEvent
     */
    public function addUpEventListener(Id $componentId, string $eventId, \Closure $onEvent): void
    {
        $this->phpListeners[(string)$componentId][$eventId] = $onEvent;
    }

    public function renderDownEventListener(Id $componentId, string $eventId, string $jsFunction): string
    {
        return <<<JS
            MBuddyListeners['{$componentId}'] = MBuddyListeners['{$componentId}'] || {};
            MBuddyListeners['{$componentId}']['{$eventId}'] = {$jsFunction};
            JS;
    }

    public function sendDownEvent(Id $componentId, string $eventId, string $value): void
    {
        $this->websocket->send(json_encode([(string)$componentId, $eventId, $value]));
    }

    public function onMessage(string $message): void
    {
        $data = json_decode($message, true);
        if (!is_array($data)) {
            return;
        }

        [$componentId, $eventId, $value] = $data;

        if (!isset($this->phpListeners[$componentId][$eventId])) {
            return;
        }

        $this->phpListeners[$componentId][$eventId]($value);
    }
}
