<?php

namespace bviguier\MBuddy;

use bviguier\RtMidi\Message;

interface Handler
{
    public function statusByte(): ?int;

    public function handle(Message $message): ?Message;
}