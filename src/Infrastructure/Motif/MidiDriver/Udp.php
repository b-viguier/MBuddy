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
    public function __construct(
        private DatagramSocket $input,
        private EncryptableSocket $output,
    ) {
    }

    /**
     * @return Promise<Udp>
     */
    public static function createAsync(
        string $inputSocket,
        string $outputSocket,
    ): Promise {
        return \Amp\call(fn(): \Generator => new self(
            DatagramSocket::bind($inputSocket),
            yield \Amp\Socket\connect($outputSocket),
        ));
    }

    public static function create(
        string $inputSocket,
        string $outputSocket,
    ): self {
        return Promise\wait(self::createAsync($inputSocket, $outputSocket));
    }

    public function send(string $message): Promise
    {
        return $this->output->write($message);
    }

    public function receive(): Promise
    {
        return call(function() {
            $data = yield $this->input->receive();
            return $data === null ? null : $data[1];
        });
    }
}
