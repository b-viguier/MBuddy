<?php

namespace bviguier\MBuddy\Device;

use bviguier\MBuddy\Device;
use bviguier\MBuddy\MidiSyxBank;
use bviguier\MBuddy\SongId;
use bviguier\RtMidi;
use Psr\Log\LoggerInterface;

class Impulse implements Device
{
    public function __construct(RtMidi\Input $input, RtMidi\Output $output, MidiSyxBank $syxBank, LoggerInterface $logger)
    {
        $this->input = $input;
        $this->output = $output;
        $this->bank = $syxBank;
        $this->onSongIdModified = function(SongId $preset): void {};
        $this->onMidiEvent = function(RtMidi\Message $msg): void {};
        $this->logger = $logger;

        $this->input->allow(RtMidi\Input::ALLOW_SYSEX);
    }

    /**
     * @return callable(SongId): void
     */
    public function doLoadSong(): callable
    {
        return function (SongId $songId): void {
            if (null === $data = $this->bank->load($songId->id())) {
                if(null === $data = $this->bank->load(SongId::DEFAULT_ID)) {
                    $this->logger->warning('Failed to load default Impulse patch');

                    return;
                }
                if(null === $patch = Device\Impulse\Patch::fromBinString($data)) {
                    $this->logger->error("Failed to create song ($songId).");
                    return;
                }
                $patch = $patch->withId($songId->id());
                $this->logger->notice("Song created ($songId) '{$patch->name()}'.");
            } else {
                if(null === $patch = Device\Impulse\Patch::fromBinString($data)) {
                    $this->logger->error("Failed to load song ($songId)).");
                    return;
                }
                $this->logger->notice("Song loaded ($songId}) '{$patch->name()}'.");
            }
            $this->currentSongId = $songId;
            $this->output->send($patch->toSysexMessage());
        };
    }

    /**
     * @param callable(SongId):void $callback
     */
    public function onSongIdModified(callable $callback): void
    {
        $this->onSongIdModified = $callback;
    }

    /**
     * @param callable(RtMidi\Message):void $callback
     */
    public function onMidiEvent(callable $callback): void
    {
        $this->onMidiEvent = $callback;
    }

    public function process(int $limit): int
    {
        for ($count = 0; $count < $limit && $msg = $this->input->pullMessage(); ++$count) {
            // Sysex handling
            switch($msg->byte(0)) {
                case self::STATUS_SYSEX:
                    $this->handleSysex($msg);
                    break;
                case self::STATUS_CC | self::CHANNEL_5:
                    switch($msg->byte(1)) {
                        case self::BUTTON_REC:
                            $this->isRecBtnPressed = $msg->byte(2) !== 0;
                            break;
                        case self::BUTTON_NEXT:
                            if($msg->byte(2) !== 0) {
                                $this->handleSongIdModification(true);
                            }
                            break;
                        case self::BUTTON_PREV:
                            if($msg->byte(2) !== 0) {
                                $this->handleSongIdModification(false);
                            }
                            break;
                        default:
                            ($this->onMidiEvent)($msg);
                    }
                    break;
                default:
                    ($this->onMidiEvent)($msg);
            }
        }

        return $count;
    }

    private RtMidi\Input $input;
    private RtMidi\Output $output;
    private MidiSyxBank $bank;

    private ?SongId $currentSongId = null;
    private bool $isRecBtnPressed = false;

    /** @var callable(SongId): void */
    private $onSongIdModified;
    /** @var callable(RtMidi\Message): void */
    private $onMidiEvent;

    private LoggerInterface $logger;

    private const STATUS_SYSEX = 0xF0;
    private const STATUS_CC = 0xB0;
    private const CHANNEL_5 = 0x04;
    private const BUTTON_REC = 0x75;
    private const BUTTON_PREV = 0x70;
    private const BUTTON_NEXT = 0x71;

    private function handleSysex(RtMidi\Message $msg): void
    {
        if(null === $patch = Device\Impulse\Patch::fromSysexMessage($msg)) {
            $this->logger->notice('Patch ignored.');

            return;
        }

        if($this->bank->save($patch->songId()->id(), $patch->name(), $patch->toBinString())) {
            $this->logger->notice("Song saved ({$patch->songId()}) '{$patch->name()}'.");
        } else {
            $this->logger->warning("Failed to save song ({$patch->songId()}) '{$patch->name()}'.");
        }
    }

    private function handleSongIdModification(bool $isNext): void
    {
        if(!$this->isRecBtnPressed) {
            return;
        }

        if($this->currentSongId === null) {
            $this->currentSongId = SongId::first();
        } else {
            $this->currentSongId = $isNext ? $this->currentSongId->next() : $this->currentSongId->previous();
        }

        $this->logger->notice("Switch to song ({$this->currentSongId->id()}).");
        ($this->onSongIdModified)($this->currentSongId);
    }
}
