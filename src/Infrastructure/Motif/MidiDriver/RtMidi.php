<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use Bveing\MBuddy\Motif\MidiDriver;
use bviguier\RtMidi as RtMidiLib;
use function Amp\delay;
use function Amp\asyncCall;

class RtMidi implements MidiDriver
{
    private array $listeners = [];

    public function __construct(
        private RtMidiLib\Input $input,
        private RtMidiLib\Output $output,
    ) {
        $input->allow(RtMidiLib\Input::ALLOW_SYSEX);
        asyncCall(fn()=>$this->loop());
    }

    public function send(string $message): void
    {
        $this->output->send(RtMidiLib\Message::fromBinString($message));
    }

    public function setListener(callable $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function removeListener(callable $listener): void
    {
        $this->listeners = array_filter(
            $this->listeners,
            fn (callable $l) => $l !== $listener
        );
    }

    private function loop(): \Generator
    {
        while (true) {
            if ($message = $this->input->pullMessage()) {
                $message = $message->toBinString();
                $listeners = $this->listeners;
                foreach ($listeners as $listener) {
                    if ($listener($message)) {
                        break;
                    }
                }
                yield delay(5 /*ms*/);
            } else {
                yield delay(500 /*ms*/);
            }
        }
    }
}
