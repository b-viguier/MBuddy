<?php

namespace bviguier\MBuddy\Impulse\Handler;

use bviguier\MBuddy\Handler;
use bviguier\RtMidi;

class Forward implements Handler
{
    public function __construct(RtMidi\Output $output)
    {
        $this->output = $output;
    }

    public function statusByte(): ?int
    {
        return null;
    }

    public function handle(RtMidi\Message $message): ?RtMidi\Message
    {
        $this->output->send($message);

        return $message;
    }

    private RtMidi\Output $output;
}
