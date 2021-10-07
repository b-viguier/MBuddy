<?php

namespace bviguier\MBuddy\WebSocket;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Message;
use Amp\Websocket\Server\ClientHandler;
use Amp\Websocket\Server\Gateway;
use Amp\Websocket\Server\Websocket;
use Monolog\Logger;
use function Amp\ByteStream\getStdout;

class Server
{
    public function __construct(string $address, int $port)
    {
        $this->websocket = new Websocket(new class($this->readBuffer) implements ClientHandler {
            /** @var array<string> */
            private array $readBuffer = [];

            /**
             * @param array<string> $readBuffer
             */
            public function __construct(array &$readBuffer) {
                $this->readBuffer = &$readBuffer;
            }

            public function handleHandshake(Gateway $gateway, Request $request, Response $response): Promise
            {
                return new Success($response);
            }

            public function handleClient(Gateway $gateway, Client $client, Request $request, Response $response): Promise
            {
                // @phpstan-ignore-next-line
                return \Amp\call(function () use ($client): \Generator {
                    while ($message = yield $client->receive()) {
                        \assert($message instanceof Message);
                        $this->readBuffer[] = yield $message->buffer();
                    }
                });
            }
        });

        Loop::run(function() use($address, $port): void {
            $sockets = [\Amp\Socket\Server::listen("$address:$port")];

            $router = new Router();
            $router->addRoute('GET', '/websocket', $this->websocket);
            $router->setFallback(new DocumentRoot(__DIR__ . '/../../web'));

            $logHandler = new StreamHandler(getStdout());
            $logHandler->setFormatter(new ConsoleFormatter());
            $logger = new Logger('server');
            $logger->pushHandler($logHandler);

            $server = new HttpServer($sockets, $router, $logger);

            $server->start();
            Loop::stop();
        });
    }

    public function tick(int $count = 1): void
    {
        Loop::run(function() use($count) {
            $wait = function(string $watcherId, $count) use(&$wait) {
                if($count <= 0) {
                    Loop::stop();
                } else {
                    Loop::defer($wait, $count-1);
                }
            };
            $wait('', $count);
        });
    }

    public function read(): ?string
    {
        return array_pop($this->readBuffer);
    }

    public function send(string $message): void
    {
        $this->websocket->broadcast($message);
    }

    public function sendBinary(string $message): void
    {
        $this->websocket->broadcastBinary($message);
    }

    private Websocket $websocket;
    /** @var array<string> */
    public array $readBuffer = [];
}
