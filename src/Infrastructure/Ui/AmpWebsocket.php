<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Ui;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Server\ClientHandler;
use Amp\Websocket\Server\Gateway;
use Bveing\MBuddy\Ui\Websocket;
use Psr\Log\LoggerInterface;
use function Amp\call;

class AmpWebsocket implements Websocket, ClientHandler
{
    private ?Client $client = null;
    private Websocket\Listener $listener;

    public function __construct(
        private string $path,
        private LoggerInterface $logger,
    ) {
        $this->listener = new Websocket\NullListener();
    }

    public function path(): string
    {
        return $this->path;
    }

    public function send(string $message): Promise
    {
        return call(function() use ($message) {
            assert($this->client !== null);
            yield $this->client->send($message);
            $this->logger->debug('Sent message: '.$message);

            return null;
        });
    }

    public function setListener(Websocket\Listener $listener): void
    {
        $this->listener = $listener;
    }

    public function handleHandshake(
        Gateway $gateway,
        Request $request,
        Response $response,
    ): Promise {
        return new Success($response);
    }

    public function handleClient(
        Gateway $gateway,
        Client $client,
        Request $request,
        Response $response,
    ): Promise {
        if ($this->client !== null) {
            $this->logger->warning('Client already set');

            return new Success();
        }
        $this->client = $client;
        $this->logger->debug('Client connected');

        return call(fn() => yield from $this->receiveLoop());
    }

    private function receiveLoop(): \Generator
    {
        try {
            while ($this->client && $message = yield $this->client->receive()) {
                $data = yield $message->buffer();
                $this->logger->debug('Received message: '.$data);
                $this->listener->onMessage($data);
            }
        } catch (\Amp\Websocket\ClosedException $e) {
            $this->logger->debug('Client Closed');
        } catch (\Throwable $e) {
            $this->logger->error('Error in receive loop: '.$e->getMessage());
        } finally {
            $this->logger->debug('Client disconnected');
            $this->client = null;
        }
    }
}
