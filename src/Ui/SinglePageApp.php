<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Options;
use Amp\Http\Server\Router;
use Amp\Promise;
use Amp\Socket\Server;
use Amp\Websocket\Server\Websocket as WebsocketServer;
use Bveing\MBuddy\Infrastructure\Ui\AmpWebsocket;
use Psr\Log\LoggerInterface;
use function Amp\call;
use function Amp\Http\Server\Middleware\stack;

class SinglePageApp
{
    private HttpServer $httpServer;
    private Websocket $websocket;

    private LoggerInterface $logger;

    private ?Component $body = null;

    /**
     * @var array<string,\WeakReference<Component>>
     */
    private array $componentsCache = [];

    public function __construct(
        private string $title = 'MBuddy',
        LoggerInterface $logger = null,
        ?string $rootDir = null,
        int $port = 8383,
    ) {
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
        \assert($this->body === null);
        \assert($this->httpServer->getState() === HttpServer::STOPPED);

        $this->body = $body;

        return $this->httpServer->start();
    }

    /**
     * @return Promise<void>
     */
    public function stop(): Promise
    {
        $this->body = null;

        return $this->httpServer->stop();
    }

    private function render(): string
    {
        \assert($this->body !== null);

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
                                    if (component === null) {
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
                            var methodName = event.target.dataset.onClick;
                            var componentId = event.target.id;
                            console.debug('click ',componentId,event.target);
                            if (componentId && methodName) {
                                MBuddySocket.send(JSON.stringify([componentId, methodName, '']));
                            }
                        });
                    };
                </script>
            </head>
            <body>
                {$this->body->render()}
            </div>
            
            </body>
            </html>
            HTML;
    }

    /**
     * @return iterable<Component>
     */
    private function findComponentsToRefresh(): iterable
    {
        \assert($this->body !== null);

        /** @var array<iterable<Component>> $ChildrenIterators */
        $ChildrenIterators = [[$this->body]];
        while ($ChildrenIterators) {
            $children = \array_pop($ChildrenIterators);
            foreach ($children as $child) {
                if ($child->isRefreshNeeded()) {
                    yield $child;
                } else {
                    $ChildrenIterators[] = $child->children();
                }
            }
        }
    }

    /**
     * @return Promise<int>
     */
    public function refresh(): Promise
    {
        return call(function() {
            $allPromises = [];
            foreach ($this->findComponentsToRefresh() as $component) {
                $allPromises[] = $this->websocket->send(
                    \json_encode([(string)$component->id(), 'refresh', $component->render()], \JSON_THROW_ON_ERROR)
                );
            }

            yield \Amp\Promise\all($allPromises);

            return \count($allPromises);
        });
    }

    private function onWebsocketMessage(string $message): void
    {
        $data = \json_decode($message, true);
        if (!\is_array($data)) {
            $this->logger->warning('Invalid message');
            return;
        }

        [$componentId, $eventId, $value] = $data;
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
        $component = $this->findComponentById(new Id($componentId));
        if ($component === null) {
            $this->logger->warning('Component not found: ' . $componentId);
            return;
        }

        $component->{$eventId}($value);

        $this->refresh();
    }

    private function findComponentById(Id $id): ?Component
    {
        \assert($this->body !== null);

        $component = ($this->componentsCache[(string)$id] ?? null)?->get();
        if ($component !== null) {
            return $component;
        }

        $this->componentsCache = [];
        $found = null;

        /** @var array<iterable<Component>> $ChildrenIterators */
        $ChildrenIterators = [[$this->body]];
        while ($ChildrenIterators) {
            $children = \array_pop($ChildrenIterators);
            foreach ($children as $child) {
                if ($child->id() == $id) {
                    $found = $child;
                }
                $this->componentsCache[(string)$child->id()] = \WeakReference::create($child);
                $ChildrenIterators[] = $child->children();
            }
        }

        return $found;
    }
}
