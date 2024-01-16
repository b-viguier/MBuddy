<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use Bveing\MBuddy\Motif\MidiDriver;
use bviguier\RtMidi as RtMidiLib;
use Amp\Promise;
use Amp\Success;

use function Amp\delay;
use function Amp\asyncCall;

class RtMidi implements MidiDriver
{
    use EventDispatcherTrait;

    private array $listeners = [];

    public function __construct(
        private RtMidiLib\Input $input,
        private RtMidiLib\Output $output,
    ) {
        $input->allow(RtMidiLib\Input::ALLOW_SYSEX);
        asyncCall(fn() => $this->loop());
    }

    public function send(string $message): Promise
    {
        $this->output->send(RtMidiLib\Message::fromBinString($message));

        return new Success();
    }

    private function loop(): \Generator
    {
        while (true) {
            if ($message = $this->input->pullMessage()) {
                $this->dispatch($message->toBinString());
                yield delay(5 /*ms*/);
            } else {
                yield delay(500 /*ms*/);
            }
        }
    }
}
