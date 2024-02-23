<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Infrastructure\Motif\MidiDriver;

use function Amp\call;
use function Amp\delay;

use Amp\Promise;

use Amp\Success;
use Bveing\MBuddy\Motif\MidiDriver;
use bviguier\RtMidi as RtMidiLib;

class RtMidi implements MidiDriver
{
    public function __construct(
        private RtMidiLib\Input $input,
        private RtMidiLib\Output $output,
    ) {
        $input->allow(RtMidiLib\Input::ALLOW_SYSEX);
    }

    public function send(string $message): Promise
    {
        $this->output->send(RtMidiLib\Message::fromBinString($message));

        return new Success();
    }

    public function receive(): Promise
    {
        return call(function() {
            while (true) {
                if ($message = $this->input->pullMessage()) {
                    return $message->toBinString();
                }
                yield delay(500 /*ms*/);
            }
        });
    }
}
