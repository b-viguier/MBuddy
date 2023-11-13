<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function () {
    $logger = yield \Bveing\Mbuddy\Infrastructure\UdpLogger::create(
        new \Amp\Socket\SocketAddress('192.168.1.11', 8484)
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

    /** @var \Amp\Http\Server\HttpServer|null $server */
    $server = null;

    $router->addRoute(
        'GET',
        '/stop',
        new \Amp\Http\Server\RequestHandler\CallableRequestHandler(function (\Amp\Http\Server\Request $request) use(&$server) {
            assert($server);
            yield $server->stop();
            yield \Amp\delay(1000);
            Amp\Loop::stop();
            return new \Amp\Http\Server\Response(\Amp\Http\Status::OK, [
                "content-type" => "text/plain; charset=utf-8",
            ], "Stop");
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
