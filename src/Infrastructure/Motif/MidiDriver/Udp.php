<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use Amp\Promise;
use Amp\Socket\DatagramSocket;
use Amp\Socket\EncryptableSocket;
use Bveing\MBuddy\Motif\MidiDriver;
use Psr\Log\LoggerInterface;
use function Amp\asyncCall;
use function Amp\Promise\wait;

class Udp implements MidiDriver
{
    use MidiDriver\DispatcherTrait;

    public function __construct(
        private string $inputSocket,
        private string $outputSocket,
        private LoggerInterface $logger,
    ) {
        $this->logger->info("Configured UDP MIDI driver with input socket {$this->inputSocket} and output socket {$this->outputSocket}");
        $this->input = DatagramSocket::bind($this->inputSocket);
        $this->output = wait(\Amp\Socket\connect($this->outputSocket));
    }


    public function send(string $message): Promise
    {
        return $this->output->write($message);
    }

    public function poll(): void
    {
        asyncCall(function() {
            $this->logger->info("Starting UDP MIDI listener on {$this->inputSocket}");

            while (true) {
                $data = yield $this->input->receive();
                if ($data === null) {
                    break;
                }
                $this->logger->debug("Received UDP MIDI packet (" . \strlen($data[1]) . " bytes) from {$data[0]}");
                asyncCall(function() use ($data) {
                    try {
                        $this->dispatch($data[1]);
                    } catch (\Throwable $e) {
                        $this->logger->error("Error dispatching MIDI message to listeners: " . $e->getMessage());
                        $this->logger->error("File: " . $e->getFile() . " Line: " . $e->getLine());
                    }
                });
            }
            $this->logger->info("UDP MIDI listener stopped");
        });
    }

    public function stopPolling(): void
    {
        $this->logger->debug("Stopping polling...");
        $this->input->close();
    }


    private DatagramSocket $input;
    private EncryptableSocket $output;
}
