<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

use Amp\Promise;
use Amp\Deferred;
use Psr\Log\LoggerInterface;

class SysexManager
{
    /** @var list<SysEx\BulkDumpBlock> */
    private array $blocksBuffer = [];

    /** @var array<string,Deferred> */
    private array $deferred = [];

    public function __construct(
        private MidiDriver $midiDriver,
        private LoggerInterface $logger,
    ) {
        $this->midiDriver->setListener([$this, 'onMessage']);
    }

    public function requestDump(SysEx\DumpRequest $request): Promise
    {
        //TODO: race timeout
        $deferred = new Deferred();
        $this->deferred[$request->getAddress()->toBinaryString()] = $deferred;

        $this->midiDriver->send((string)$request->toSysex());

        return $deferred->promise();
    }

    /**
     * @param iterable<SysEx\BulkDumpBlock> $blocks
     * @return Promise<array<array-key,int>>
     */
    public function sendDump(iterable $blocks): Promise
    {
        $promises = [];
        foreach ($blocks as $block) {
            $promises[] = $this->midiDriver->send((string)$block);
        }

        return Promise\all($promises);
    }

    public function requestParameter(SysEx\ParameterRequest $request): Promise
    {
        //TODO: race timeout
        $deferred = new Deferred();
        $this->deferred[$request->getAddress()->toBinaryString()] = $deferred;

        $this->midiDriver->send((string)$request->toSysex());

        return $deferred->promise();
    }

    public function sendParameter(SysEx\ParameterChange $change): void
    {
        $this->midiDriver->send((string)$change);
    }

    public function onMessage(string $message): bool
    {
        $sysex = SysEx::fromBinaryString($message);
        if ($sysex === null) {
            return false;
        }

        switch ($sysex->getDeviceNumber()) {
            case Sysex\BulkDumpBlock::DEVICE_NUMBER:
                $this->handleBulkDumpBlock(Sysex\BulkDumpBlock::fromSysex($sysex));
                break;
            case Sysex\ParameterChange::DEVICE_NUMBER:
                $this->handleParameterChange(SysEx\ParameterChange::fromSysex($sysex));
                break;
            default:
                $this->logger->warning('Received unknown SysEx message', ['message' => $sysex]);
                return false;
        }

        return true;
    }

    private function handleBulkDumpBlock(SysEx\BulkDumpBlock $block): void
    {
        if (!$this->blocksBuffer) {
            if ($block->isHeaderBlock()) {
                $this->blocksBuffer[] = $block;
            } else {
                $this->logger->warning('Received BulkDumpBlock without current dump');
            }
        } else {
            if ($block->isFooterBlock() && $block->getAddress()->equals($this->blocksBuffer[0]->getAddress())) {
                $this->blocksBuffer[] = $block;

                $deferredKey = $block->getAddress()->toBinaryString();
                $deferred = $this->deferred[$deferredKey];
                $buffer = $this->blocksBuffer;
                unset($this->deferred[$deferredKey], $buffer);
                $this->blocksBuffer = [];

                $deferred->resolve($this->blocksBuffer);
            } else {
                if ($block->isHeaderBlock()) {
                    $this->logger->warning('Received BulkDumpBlock while current dump is incomplete');
                } else {
                    $this->blocksBuffer[] = $block;
                }
            }
        }
    }

    private function handleParameterChange(SysEx\ParameterChange $change): void
    {
        $deferredKey = $change->getAddress()->toBinaryString();
        $deferred = $this->deferred[$deferredKey];
        unset($this->deferred[$deferredKey]);
        $deferred->resolve($change);
    }
}
