<?php

declare(strict_types=1);

namespace Bveing\MBuddy\DevTools;

use Amp\Socket\EncryptableSocket;
use Bveing\MBuddy\Motif\MidiDriver;
use Bveing\MBuddy\Motif\MidiListener;
use Psr\Log\LoggerInterface;
use function Amp\Promise\rethrow;
use function Amp\Promise\wait;

class MidiUdpForwarder implements MidiListener
{
    public function __construct(
        private MidiDriver $inputDriver,
        private string $outputSocket,
        private LoggerInterface $logger,
    ) {
        $this->logger->debug("Starting MIDI UDP Forwarder from input driver to {$this->outputSocket}");
        $this->output = wait(\Amp\Socket\connect($this->outputSocket));

        $this->inputDriver->addListener($this);
    }

    public function onMidiMessage(string $message): bool
    {
        $this->logger->debug("Forwarding MIDI message");
        rethrow($this->output->write($message));
        return true;
    }

    private EncryptableSocket $output;
}
