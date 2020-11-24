<?php

namespace bviguier\MBuddy\Pa50\Handler;

use bviguier\MBuddy\Handler;
use bviguier\RtMidi\Message;

class BankChange implements Handler
{
    public function __construct(ProgramChange $programChangeHandler)
    {
        $this->prgChangeHandler = $programChangeHandler;
    }

    public function statusByte(): ?int
    {
        // CC on channel 16
        return 0xBF;
    }

    public function handle(Message $message): ?Message
    {
        assert($message->byte(0) === $this->statusByte());
        assert($message->size() === 3);

        switch($message->byte(1)) {
            case 0x00: //MSB
                $this->msb = $message->byte(2);
                break;
            case 0x20: //LSB
                $this->lsb = $message->byte(2);
                break;
            default:
                return $message;
        }

        $this->prgChangeHandler->enable($this->lsb === 0 && $this->msb === 0);

        return null;
    }

    private ProgramChange $prgChangeHandler;
    private int $lsb = -1;
    private int $msb = -1;
}
