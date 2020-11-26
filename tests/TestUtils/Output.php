<?php

namespace bviguier\tests\MBuddy\TestUtils;

use bviguier\RtMidi;

class Output implements RtMidi\Output
{
    /** @var array<RtMidi\Message> */
    public array $msgStack = [];

    public function name(): string
    {
        return 'Fake MBuddy Output';
    }

    public function send(RtMidi\Message $message): void
    {
        $this->msgStack[] = $message;
    }

}
