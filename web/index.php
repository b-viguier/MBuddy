<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function () {
    $sockets = [
        \Amp\Socket\Server::listen("0.0.0.0:8383"),
    ];

    $server = new \Amp\Http\Server\HttpServer(
        $sockets,
        new \Amp\Http\Server\RequestHandler\CallableRequestHandler(function (\Amp\Http\Server\Request $request) {
            return new \Amp\Http\Server\Response(\Amp\Http\Status::OK, [
                "content-type" => "text/plain; charset=utf-8",
            ], "Hello, World!");
        }),
        new \Psr\Log\NullLogger(),
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
