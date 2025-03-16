<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function () {
    $kernel = new \Bveing\MBuddy\App\Kernel(environment: 'dev', debug: true);
    $sfHttpBridge = new \Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory();

    $kernel->boot();
    $container = $kernel->getContainer();
    $logger = $container->get('public_logger');


    $server = new \Amp\Http\Server\HttpServer(
        [
            \Amp\Socket\Server::listen("0.0.0.0:8383"),
        ],
        \Amp\Http\Server\Middleware\stack(
            new \Amp\Http\Server\RequestHandler\CallableRequestHandler(
                function (\Amp\Http\Server\Request $request) use($sfHttpBridge, $kernel) {

                    $client = $request->getClient()->getRemoteAddress();
                    $severParams = [
                        'REMOTE_ADDR' => $client->getHost(),
                        'REMOTE_PORT' => $client->getPort(),
                    ];

                    $serverRequest = new \React\Http\Message\ServerRequest(
                        $request->getMethod(),
                        (string) $request->getUri(),
                        $request->getHeaders(),
                        yield $request->getBody()->buffer(),
                        $request->getProtocolVersion(),
                        $severParams + $_SERVER
                    );

                    $requestBodyParser = new \React\Http\Middleware\RequestBodyParserMiddleware();

                    $sfRequest = $sfHttpBridge->createRequest($requestBodyParser($serverRequest, fn($r) => $r));
                    $sfResponse = $kernel->handle($sfRequest);

                    try {
                        $response = new \Amp\Http\Server\Response(
                            $sfResponse->getStatusCode(),
                            $sfResponse->headers->all()
                        );

                        if ($sfResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse
                            || $sfResponse instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
                        ) {
                            $response->setBody(
                                new \Amp\ByteStream\IteratorStream(
                                    new \Amp\Producer(function (callable $emit) use ($sfResponse) {
                                        ob_start(function ($buffer) use ($emit) {
                                            yield $emit($buffer);

                                            return '';
                                        });
                                        $sfResponse->sendContent();
                                        ob_end_clean();
                                    })
                                )
                            );
                        } else {
                            $response->setBody($sfResponse->getContent());
                        }

                        return $response;
                    } finally {
                        if ($kernel instanceof \Symfony\Component\HttpKernel\TerminableInterface) {
                            $kernel->terminate($sfRequest, $sfResponse);
                            // TODO: clean logs
                        }
                    }
                }
            ),
        ),
        $logger,
        (new \Amp\Http\Server\Options())->withDebugMode(),
    );

    // Stop the server gracefully when SIGINT is received.
    // This is technically optional, but it is best to call Server::stop()
    if (\extension_loaded("pcntl")) {
        \Amp\Loop::onSignal(SIGINT, function (string $watcherId) use ($server) {
            \Amp\Loop::cancel($watcherId);
            yield $server->stop();
        });
    }


    yield $server->start();
});
