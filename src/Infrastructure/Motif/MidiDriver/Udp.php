<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use Bveing\MBuddy\Motif\MidiDriver;
use Amp\Socket\DatagramSocket;
use Amp\Socket\EncryptableSocket;
use Amp\Promise;

use function Amp\delay;
use function Amp\asyncCall;
use function Amp\call;

class Udp implements MidiDriver
{
    public function __construct(
        private DatagramSocket $input,
        private EncryptableSocket $output,
    ) {
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
