<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function () {
    $logger = yield \Bveing\Mbuddy\Infrastructure\UdpLogger::create(
        new \Amp\Socket\SocketAddress('192.168.1.11', 8484),
    );

    $websocket = new Amp\Websocket\Server\Websocket(
        new class($logger) implements \Amp\Websocket\Server\ClientHandler {
            public function __construct(private \Psr\Log\LoggerInterface $logger)
            {
            }
            public function handleHandshake(
                \Amp\Websocket\Server\Gateway $gateway,
                \Amp\Http\Server\Request $request,
                \Amp\Http\Server\Response $response,
            ): Amp\Promise {
                return new \Amp\Success($response);
            }

            public function handleClient(
                \Amp\Websocket\Server\Gateway $gateway,
                \Amp\Websocket\Client $client,
                \Amp\Http\Server\Request $request,
                \Amp\Http\Server\Response $response,
            ): \Amp\Promise {
                return \Amp\call(function() use ($gateway, $client): \Generator {
                    while ($message = yield $client->receive()) {
                        $string = yield $message->buffer();
                        $this->logger->info($string);
                        $gateway->broadcast($string);
                    }
                });
            }

        },
    );

    $sockets = [
        \Amp\Socket\Server::listen("0.0.0.0:8383"),
    ];

    $router = new Amp\Http\Server\Router;

    $router->addRoute(
        'GET',
        '/',
        new \Amp\Http\Server\RequestHandler\CallableRequestHandler(function (\Amp\Http\Server\Request $request) {
            return new \Amp\Http\Server\Response(\Amp\Http\Status::OK, [
                "content-type" => "text/plain; charset=utf-8",
            ], "Hello, World!");
        }),
    );

    $router->addRoute('GET', '/websocket', $websocket);

    /** @var \Amp\Http\Server\HttpServer|null $server */
    $server = null;

    $router->addRoute(
        'GET',
        '/stop',
        new \Amp\Http\Server\RequestHandler\CallableRequestHandler(
            function (\Amp\Http\Server\Request $request) use (&$server) {
                assert($server);
                yield $server->stop();
                yield \Amp\delay(1000);
                Amp\Loop::stop();

                return new \Amp\Http\Server\Response(\Amp\Http\Status::OK, [
                    "content-type" => "text/plain; charset=utf-8",
                ], "Stop");
            },
        ),
    );

    $router->addRoute(
        'GET',
        '/midi',
        new \Amp\Http\Server\RequestHandler\CallableRequestHandler(function (\Amp\Http\Server\Request $request) {
            /** @var \Amp\Socket\EncryptableSocket $out */
            $out = yield \Amp\Socket\connect("udp://127.0.0.1:8123");
            /** @var  $in */
            $in = \Amp\Socket\DatagramSocket::bind('udp://127.0.0.1:8321');

            yield $out->write(pack("C*", 0b10010000, 0b0001000, 0b01111111));
            [, $data] = yield $in->receive();
            $bytes = unpack("C*", $data);

            return new \Amp\Http\Server\Response(\Amp\Http\Status::OK, [
                "content-type" => "text/plain; charset=utf-8",
            ], implode(",", $bytes));
        }),
    );

    // Asynchronous FileSystem incompatible with PhpWin
    $fileSystem = new \Amp\File\Filesystem(new \Amp\File\Driver\BlockingDriver());

    // TODO: incompatible with files containing spaces
    $documentRoot = new \Amp\Http\Server\StaticContent\DocumentRoot(
        __DIR__.'/',
        $fileSystem,
    );
    $router->addRoute(
        'GET',
        '/scores/{name}',
        $documentRoot,
    );
    $router->addRoute(
        'GET',
        '/app.html',
        $documentRoot,
    );


    $server = new \Amp\Http\Server\HttpServer(
        $sockets,
        Amp\Http\Server\Middleware\stack($router, new \Amp\Http\Server\Middleware\ExceptionMiddleware()),
        $logger,
        (new \Amp\Http\Server\Options)->withDebugMode(),
    );

    yield $server->start();

    // Stop the server gracefully when SIGINT is received.
    // This is technically optional, but it is best to call Server::stop()
    if (\extension_loaded("pcntl")) {
        Amp\Loop::onSignal(SIGINT, function (string $watcherId) use ($server) {
            Amp\Loop::cancel($watcherId);
            yield $server->stop();
        });
    }

});

echo "Server stopped\n";
