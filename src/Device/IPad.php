<?php

namespace bviguier\MBuddy\Device;

use bviguier\RtMidi;
use bviguier\MBuddy\Device;
use bviguier\MBuddy\SongId;
use Psr\Log\LoggerInterface;

class IPad implements Device
{
    public function __construct(RtMidi\Output $output, LoggerInterface $logger)
    {
        $this->output = $output;
        $this->logger = $logger;
    }

    public function process(int $limit): int
    {
        return 0;
    }

    /**
     * @return callable(SongId): void
     */
    public function doLoadSong(): callable
    {
        return function (SongId $songId): void {
            $this->output->send(RtMidi\Message::fromIntegers(
                self::STATUS_PC | self::CHANNEL_16,
                $songId->id()
            ));
            $this->logger->notice("Song loaded ($songId).");
        };
    }

    private RtMidi\Output $output;
    private LoggerInterface $logger;

    private const STATUS_PC = 0xC0;
    private const CHANNEL_16 = 0x0F;
}