<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use Amp\Promise;
use Amp\Socket\DatagramSocket;
use Amp\Socket\EncryptableSocket;
use Bveing\MBuddy\Motif\MidiDriver;
use Psr\Log\LoggerInterface;
use function Amp\call;
use function Amp\Promise\rethrow;

class Udp implements MidiDriver
{
    use MidiDriver\DispatcherTrait;

    public function __construct(
        private string $inputSocket,
        private string $outputSocket,
        private LoggerInterface $logger,
    ) {
        $this->logger->info("Configured UDP MIDI driver with input socket {$this->inputSocket} and output socket {$this->outputSocket}");
    }


    public function send(string $message): Promise
    {
        return $this->output !== null
            ? $this->output->write($message)
            : \Amp\call(function() use ($message) {
                $this->output = yield \Amp\Socket\connect($this->outputSocket);
                return yield $this->output->write($message);
            });
    }

    public function poll(): void
    {
        rethrow(call(function() {
            $this->logger->info("Starting UDP MIDI listener on {$this->inputSocket}");
            $this->input = DatagramSocket::bind($this->inputSocket);
            while (true) {
                \assert($this->input !== null);
                $data = yield $this->input->receive();
                if ($data === null) {
                    break;
                }
                $this->logger->debug("Received UDP MIDI packet (" . \strlen($data[1]) . " bytes) from {$data[0]}");
                $this->dispatch($data[1]);
            }
            $this->input = null;
            $this->logger->info("UDP MIDI listener stopped");
        }));
    }

    public function stopPolling(): void
    {
        $this->input?->close();
    }


    private ?DatagramSocket $input = null;
    private ?EncryptableSocket $output = null;
}
