<?php

namespace bviguier\MBuddy\Device;

use bviguier\MBuddy\Device;
use bviguier\MBuddy\SongId;
use bviguier\RtMidi;
use Psr\Log\LoggerInterface;

class Pa50 implements Device
{
    public const MBUDDY_BANK_MSB = 0x01;
    public const MBUDDY_BANK_LSB = 0x00;

    public function __construct(RtMidi\Input $input, RtMidi\Output $output, LoggerInterface $logger)
    {
        $this->input = $input;
        $this->output = $output;
        $this->onSongChanged = function(SongId $id): void {};
        $this->logger = $logger;
    }

    /**
     * @param callable(SongId): void $callback
     */
    public function onSongChanged(callable $callback): void
    {
        $this->onSongChanged = $callback;
    }

    /**
     * @return callable(SongId):void
     */
    public function doModifySongIdOfCurrentPerformance(): callable
    {
        return function (SongId $songId): void {
            $this->output->send(RtMidi\Message::fromIntegers(
                self::STATUS_CC | self::CHANNEL_16, self::CC_BANK_MSB, self::MBUDDY_BANK_MSB
            ));
            $this->output->send(RtMidi\Message::fromIntegers(
                self::STATUS_CC | self::CHANNEL_16, self::CC_BANK_LSB, self::MBUDDY_BANK_LSB
            ));
            $this->output->send(RtMidi\Message::fromIntegers(
                self::STATUS_PC | self::CHANNEL_16, $songId->id()
            ));
            $this->logger->notice("Modified attached SongID ($songId)");
        };
    }

    public function doChangeSong(SongId $songId): void
    {
        // On Control Channel:
        // MSB: 0, LSB: 17 + SongId/16, PC: songId%16

        $this->output->send(RtMidi\Message::fromIntegers(
            self::STATUS_CC | self::CONTROL_CHANNEL, self::CC_BANK_MSB, 0
        ));
        $this->output->send(RtMidi\Message::fromIntegers(
            self::STATUS_CC | self::CONTROL_CHANNEL, self::CC_BANK_LSB, 17 + intdiv($songId->id(), 16)
        ));
        $this->output->send(RtMidi\Message::fromIntegers(
            self::STATUS_PC | self::CONTROL_CHANNEL, $songId->id() % 16
        ));
        $this->logger->notice("Changed Song ($songId)");
    }

    /**
     * @return callable(RtMidi\Message):void
     */
    public function doPlayEvent(): callable
    {
        return [$this->output, 'send'];
    }

    public function process(int $limit): int
    {
        for ($count = 0; $count < $limit && $msg = $this->input->pullMessage(); ++$count) {
            switch ($msg->byte(0)) {
                case self::STATUS_CC | self::CHANNEL_16:
                    switch ($msg->byte(1)) {
                        case self::CC_BANK_MSB:
                            $this->lastMSB = $msg->byte(2);
                            break;
                        case self::CC_BANK_LSB:
                            $this->lastLSB = $msg->byte(2);
                            break;
                    }
                    break;
                case self::STATUS_PC | self::CHANNEL_16:
                    if($this->lastMSB !== self::MBUDDY_BANK_MSB || $this->lastLSB !== self::MBUDDY_BANK_LSB) {
                        $this->logger->notice("Performance ignored");
                    } else {
                        $songId = new SongId($msg->byte(1));
                        $this->logger->notice("Song changed ($songId)");
                        ($this->onSongChanged)($songId);
                    }
                    $this->lastMSB = $this->lastLSB = null;
                    break;
            }
        }

        return $count;
    }

    private RtMidi\Input $input;
    private RtMidi\Output $output;

    private ?int $lastMSB = null;
    private ?int $lastLSB = null;

    /** @var callable(SongId): void */
    private $onSongChanged;

    private LoggerInterface $logger;

    private const STATUS_CC = 0xB0;
    private const STATUS_PC = 0xC0;
    private const CHANNEL_16 = 0x0F;
    private const CONTROL_CHANNEL = 0x0E;
    private const CC_BANK_MSB = 0x00;
    private const CC_BANK_LSB = 0x20;
}
