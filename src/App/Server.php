<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App;

use Amp\ByteStream\IteratorStream;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Options;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Loop;
use Amp\Producer;
use Amp\Socket\Server as SocketServer;
use Psr\Log\LoggerInterface;
use React\Http\Message\ServerRequest;
use React\Http\Middleware\RequestBodyParserMiddleware;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use function Amp\Http\Server\Middleware\stack;

class Server
{
    public static function start(string $env, bool $debug): void
    {
        $kernel = new Kernel($env, $debug);
        $kernel->boot();

        $server = $kernel->getContainer()->get(self::class);
        if (!$server instanceof self) {
            throw new \RuntimeException('Server not found in container');
        }
        $server->run();

        $kernel->shutdown();
    }

    public function __construct(
        private KernelInterface $kernel,
        private int $port,
        private LoggerInterface $logger,
        private HttpFoundationFactory $httpBridge,
        private RequestBodyParserMiddleware $requestBodyParser,
    )
    {
    }

    private function run(): void
    {
        Loop::run(function () {
            $httpServer = new HttpServer(
                [
                    SocketServer::listen("0.0.0.0:{$this->port}"),
                ],
                stack(
                    new CallableRequestHandler(\Closure::fromCallable([$this, 'handleRequest'])),
                ),
                $this->logger,
                (new Options())->withDebugMode(), // TODO: only in dev
            );

            // Stop the server gracefully when SIGINT is received.
            // This is technically optional, but it is best to call Server::stop()
            if (\extension_loaded("pcntl")) {
                \Amp\Loop::onSignal(SIGINT, function (string $watcherId) use ($httpServer) {
                    \Amp\Loop::cancel($watcherId);
                    yield $httpServer->stop();
                });
            }


            yield $httpServer->start();
        });
    }

    private function handleRequest(Request $request): \Generator
    {
        $client = $request->getClient()->getRemoteAddress();
        $severParams = [
            'REMOTE_ADDR' => $client->getHost(),
            'REMOTE_PORT' => $client->getPort(),
        ];

        $serverRequest = new ServerRequest(
            $request->getMethod(),
            (string) $request->getUri(),
            $request->getHeaders(),
            yield $request->getBody()->buffer(),
            $request->getProtocolVersion(),
            $severParams + $_SERVER
        );

        $sfRequest = $this->httpBridge->createRequest(
            ($this->requestBodyParser)($serverRequest, fn($r) => $r)
        );
        $sfResponse = $this->kernel->handle($sfRequest);

        try {
            $response = new Response(
                $sfResponse->getStatusCode(),
                $sfResponse->headers->all(),    // @phpstan-ignore-line
            );

            if ($sfResponse instanceof StreamedResponse
                || $sfResponse instanceof BinaryFileResponse
            ) {
                ob_start(flags: \PHP_OUTPUT_HANDLER_REMOVABLE);
                $sfResponse->sendContent();
                $response->setBody(ob_get_clean() ?: null);
            } else {
                $response->setBody($sfResponse->getContent() ?: null);
            }

            return $response;
        } finally {
            if ($this->kernel instanceof TerminableInterface) {
                $this->kernel->terminate($sfRequest, $sfResponse);
                \gc_collect_cycles();
            }
        }
    }
}
