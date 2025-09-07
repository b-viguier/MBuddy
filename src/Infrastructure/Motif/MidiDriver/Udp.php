<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use Amp\Promise;
use Amp\Socket\DatagramSocket;
use Amp\Socket\EncryptableSocket;
use Bveing\MBuddy\Motif\MidiDriver;
use function Amp\call;

class Udp implements MidiDriver
{
    use MidiDriver\DispatcherTrait;

    public function __construct(
        private string $inputSocket,
        private string $outputSocket,
    ) {
        $this->input = DatagramSocket::bind($this->inputSocket);
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

    public function poll(): Promise
    {
        return call(function() {
            while (true) {
                $data = yield $this->input->receive();
                if ($data === null) {
                    break;
                }
                $this->dispatch($data[1]);
            }
        });
    }

    public function stopPolling(): void
    {
        $this->input->close();
    }


    private DatagramSocket $input;
    private ?EncryptableSocket $output = null;
}
