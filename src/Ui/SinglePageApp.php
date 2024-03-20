<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Options;
use Amp\Http\Server\Router;
use Amp\Promise;
use Amp\Socket\Server;
use Amp\Success;
use Amp\Websocket\Server\Websocket as WebsocketServer;
use Bveing\MBuddy\Infrastructure\Ui\AmpWebsocket;
use Psr\Log\LoggerInterface;
use function Amp\call;
use function Amp\Http\Server\Middleware\stack;

class SinglePageApp implements SinglePageApp\Renderer
{
    public function __construct(
        private string $title = 'MBuddy',
        LoggerInterface $logger = null,
        ?string $rootDir = null,
        int $port = 8383,
    ) {
        $this->root = null;
        $this->logger = $logger ?? new \Psr\Log\NullLogger();
        $this->websocket = new AmpWebsocket('/websocket', $this->logger);
        $websocketServer = new WebsocketServer($this->websocket);
        $router = new Router();

        $this->websocket->setListener(
            new class (\Closure::fromCallable([$this, 'onWebsocketMessage'])) implements Websocket\Listener {
                public function __construct(
                    private \Closure $callback,
                ) {
                }

                public function onMessage(string $message): void
                {
                    ($this->callback)($message);
                }
            }
        );

        $this->httpServer = new \Amp\Http\Server\HttpServer(
            [
                Server::listen("0.0.0.0:$port"),
            ],
            stack($router, new \Amp\Http\Server\Middleware\ExceptionMiddleware()),
            $this->logger,
            (new Options())->withDebugMode(),
        );

        $router->addRoute('GET', $this->websocket->path(), $websocketServer);

        $router->addRoute(
            'GET',
            '/stop',
            new \Amp\Http\Server\RequestHandler\CallableRequestHandler(
                function(\Amp\Http\Server\Request $request) {
                    // TODO: how to stop main loop?
                    yield $this->stop();

                    return new \Amp\Http\Server\Response(\Amp\Http\Status::OK, [
                        "content-type" => "text/plain; charset=utf-8",
                    ], "Stop");
                },
            ),
        );

        $router->addRoute(
            'GET',
            '/',
            new \Amp\Http\Server\RequestHandler\CallableRequestHandler(function(\Amp\Http\Server\Request $request) {

                return new \Amp\Http\Server\Response(
                    \Amp\Http\Status::OK,
                    [
                        "content-type" => "text/html; charset=utf-8",
                    ],
                    $this->render(),
                );
            }),
        );

        // Asynchronous FileSystem incompatible with PhpWin
        $fileSystem = new \Amp\File\Filesystem(new \Amp\File\Driver\BlockingDriver());

        $router->addRoute(
            'GET',
            '/mbuddy-assets/{path:.+}',
            new \Amp\Http\Server\StaticContent\DocumentRoot(
                __DIR__,
                $fileSystem,
            ),
        );

        if ($rootDir !== null) {
            // TODO: incompatible with files containing spaces
            $router->setFallback(
                new \Amp\Http\Server\StaticContent\DocumentRoot(
                    $rootDir,
                    $fileSystem,
                )
            );
        }


        // Stop the server gracefully when SIGINT is received.
        // This is technically optional, but it is best to call Server::stop()
        if (\extension_loaded("pcntl")) {
            \Amp\Loop::onSignal(SIGINT, function(string $watcherId) {
                \Amp\Loop::cancel($watcherId);
                yield $this->stop();
            });
        }
    }

    /**
     * @return Promise<void>
     */
    public function start(Component $body): Promise
    {
        \assert($this->httpServer->getState() === HttpServer::STOPPED);

        $this->root = SinglePageApp\Node::fromComponent($body, $this);

        return $this->httpServer->start();
    }

    /**
     * @return Promise<void>
     */
    public function stop(): Promise
    {
        $this->root = null;

        return $this->httpServer->stop();
    }

    public function scheduleRefresh(): void
    {
        if($this->refreshScheduled) {
            return;
        }

        $this->refreshScheduled = true;
        \Amp\Loop::delay(self::REFRESH_DELAY_MS, function() {
            $this->refreshScheduled = false;
            Promise\rethrow($this->refresh());
        });
    }

    /**
     * @return Promise<int>
     */
    public function refresh(): Promise
    {
        if ($this->root === null) {
            return new Success(0);
        }

        return call(function() {
            $allPromises = [];
            \assert($this->root !== null);
            foreach ($this->root->update() as $fragment) {
                $allPromises[] = $this->websocket->send(
                    \json_encode([(string)$fragment->id(), 'refresh', $fragment->content()], \JSON_THROW_ON_ERROR)
                );
            }

            yield \Amp\Promise\all($allPromises);

            return \count($allPromises);
        });
    }

    private const REFRESH_DELAY_MS = 20;

    private HttpServer $httpServer;
    private Websocket $websocket;

    private LoggerInterface $logger;

    private ?SinglePageApp\Node $root;

    private bool $refreshScheduled = false;

    private function render(): string
    {
        \assert($this->root !== null);
        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="translucent">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            
                <link rel="stylesheet" href="mbuddy-assets/css/bootstrap.css" crossorigin="anonymous">
                <link rel="stylesheet" href="mbuddy-assets/css/bootstrap-icons.css" crossorigin="anonymous">
                
                <script src="mbuddy-assets/js/jquery.js" crossorigin="anonymous"></script>
                <script src="mbuddy-assets/js/popper.js" crossorigin="anonymous"></script>
                <script src="mbuddy-assets/js/bootstrap.js" crossorigin="anonymous"></script>
                
                <style>
                    html,
                    body {
                      height: 100%;
                    }
                    
                    body {
                      display: -ms-flexbox;
                      display: flex;
                    }
                </style>

                <title>{$this->title}</title>
                
                <script>
                    var MBuddySocket = new WebSocket(
                        "ws://" + window.location.hostname + ":" + window.location.port + '{$this->websocket->path()}'
                    );
                    MBuddySocket.binaryType = "arraybuffer";
                
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
                            var [id, eventId, content] = msg;
                           
                            switch (eventId) {
                                case 'refresh':
                                    var component = $('#'+id);
                                    if (component.length === 0) {
                                        console.error('Component not found: ' + id);
                                        return;
                                    }
                                    component.replaceWith(content);
                                    break;
                                default:
                                    console.error('Unknown event: ' + eventId);
                            }
                        });
                        
                        $(document).on('click', '[data-on-click]', function(event) {
                            var target = event.target.closest('[data-on-click]');
                            var methodName = target.dataset.onClick;
                            var value = target.dataset.value || '';
                            var componentId = target.dataset.targetId || target.id;
                            if (componentId && methodName) {
                                MBuddySocket.send(JSON.stringify([componentId, methodName, value]));
                            } else {
                                console.warn('Invalid event', componentId, methodName, value);
                            }
                        });
                    };
                </script>
            </head>
            <body>
                {$this->root->render()}
            </div>
            
            </body>
            </html>
            HTML;
    }

    private function onWebsocketMessage(string $message): void
    {
        $data = \json_decode($message, true);
        if (!\is_array($data)) {
            $this->logger->warning('Invalid message');
            return;
        }

        [$componentId, $eventId, $value] = $data;
        \assert(\is_string($componentId) && \is_string($eventId) && \is_string($value));
        \assert($componentId !== '' && $eventId !== '');

        if ($componentId === 'console') {
            switch($eventId) {
                case 'log': $this->logger->info("[Console] $value");
                    break;
                case 'warn': $this->logger->warning("[Console] $value");
                    break;
                case 'error': $this->logger->error("[Console] $value");
                    break;
                case 'critical': $this->logger->critical("[Console] $value");
                    break;
                default: $this->logger->warning("[Console] Unknown method: $eventId");
            }
            return;
        }

        $component = $this->root?->findComponent(new Id($componentId));
        if ($component === null) {
            $this->logger->warning('Component not found: ' . $componentId);
            return;
        }

        $callback = [$component, $eventId];
        \assert(\is_callable($callback));
        \call_user_func($callback, $value);
    }
}
