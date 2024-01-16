<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use Bveing\MBuddy\Motif\MidiDriver;
use Amp\Socket\DatagramSocket;
use Amp\Socket\EncryptableSocket;
use function Amp\delay;
use function Amp\asyncCall;
use Amp\Promise;

class Udp implements MidiDriver
{
    use EventDispatcherTrait;

    private array $listeners = [];

    public function __construct(
        private DatagramSocket $input,
        private EncryptableSocket $output,
    ) {
        asyncCall(fn()=>$this->loop());
    }

    public function send(string $message): Promise
    {
        return $this->output->write($message);
    }

    private function loop(): \Generator
    {
        while (true) {
            [, $data] = yield $this->input->receive();
            $this->dispatch($data);
        }
    }
}
