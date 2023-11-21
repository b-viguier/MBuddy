<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Ui;

use Bveing\MBuddy\Ui\Websocket;
use Bveing\MBuddy\Ui\WebsocketListener;
use Amp\Websocket\Server\ClientHandler;
use Amp\Promise;
use Amp\Websocket\Server\Gateway;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Success;
use Amp\Websocket\Client;
use function Amp\call;
use Psr\Log\LoggerInterface;

class AmpWebsocket implements Websocket, ClientHandler
{
    private ?Client $client = null;
    private ?WebsocketListener $listener = null;

    public function __construct(
        private string $uri,
        private LoggerInterface $logger,
    ) {
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function send(string $message): void
    {
        assert($this->client !== null);
        $this->client->send($message);
        $this->logger->debug('Sent message: '.$message);
    }

    public function setListener(WebsocketListener $listener): void
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

        return call(fn() => $this->receiveLoop());
    }

    private function receiveLoop(): \Generator
    {
        try {
            while ($message = yield $this->client->receive()) {
                $data = yield $message->buffer();
                $this->logger->debug('Received message: '.$data);
                if ($this->listener !== null) {
                    $this->listener->onMessage($data);
                }
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