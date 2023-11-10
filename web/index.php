<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function () {
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
        new class() extends \Psr\Log\AbstractLogger {
            public function log($level, $message, array $context = []): void
            {
                echo $message."\n";
                !$context ?: var_dump($context);
            }
        },
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
