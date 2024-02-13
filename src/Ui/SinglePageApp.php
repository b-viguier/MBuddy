<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui;

use Amp\Promise;
use Psr\Log\LoggerInterface;
use Bveing\MBuddy\Infrastructure\Ui\AmpWebsocket;
use Amp\Websocket\Server\Websocket as WebsocketServer;
use Amp\Http\Server\Router;
use Amp\Http\Server\HttpServer;
use Amp\Socket\Server;
use Amp\Http\Server\Options;

use function Amp\Http\Server\Middleware\stack;

class SinglePageApp
{
    private HttpServer $httpServer;
    private JsEventBus $jsEventBus;

    private ?Component $body = null;

    public function __construct(
        private string $title,
        private LoggerInterface $logger,
        int $port = 8383,
    ) {

        $websocket = new AmpWebsocket('/websocket', $this->logger);
        $websocketServer = new WebsocketServer($websocket);
        $this->jsEventBus = new JsEventBus($websocket, $this->logger);
        $router = new Router();

        $this->httpServer = new \Amp\Http\Server\HttpServer(
            [
                Server::listen("0.0.0.0:$port"),
            ],
            stack($router, new \Amp\Http\Server\Middleware\ExceptionMiddleware()),
            $logger,
            (new Options())->withDebugMode(),
        );

        $router->addRoute('GET', $websocket->getPath(), $websocketServer);

        $router->addRoute(
            'GET',
            '/stop',
            new \Amp\Http\Server\RequestHandler\CallableRequestHandler(
                function(\Amp\Http\Server\Request $request) use (&$server) {
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

        // TODO: incompatible with files containing spaces
        $documentRoot = new \Amp\Http\Server\StaticContent\DocumentRoot(
            __DIR__.'/',
            $fileSystem,
        );
        $router->setFallback($documentRoot);


        // Stop the server gracefully when SIGINT is received.
        // This is technically optional, but it is best to call Server::stop()
        if (\extension_loaded("pcntl")) {
            \Amp\Loop::onSignal(SIGINT, function(string $watcherId) use ($server) {
                \Amp\Loop::cancel($watcherId);
                yield $this->stop();
            });
        }
    }

    public function start(Component $body): Promise
    {
        assert($this->body === null);
        assert($this->httpServer->getState() === HttpServer::STOPPED);

        $this->body = $body;

        return $this->httpServer->start();
    }

    public function stop(): Promise
    {
        $this->body = null;

        return $this->httpServer->stop();
    }

    private function render(): string
    {
        assert($this->body !== null);

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
