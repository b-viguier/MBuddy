<?php

namespace bviguier\tests\MBuddy\TestUtils;

use bviguier\RtMidi;

class Input implements RtMidi\Input
{
    /** @var array<RtMidi\Message> */
    public array $msgStack = [];

    public function name(): string
    {
        return 'Fake MBuddy Input';
    }

    public function allow(int $allowMask): void
    {
        return;
    }

    public function pullMessage(): ?RtMidi\Message
    {
        return array_shift($this->msgStack);
    }
}
