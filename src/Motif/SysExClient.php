<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

use Amp\Promise;
use Amp\Deferred;
use Psr\Log\LoggerInterface;

class SysExClient
{
    /** @var list<SysEx\BulkDumpBlock> */
    private array $blocksBuffer = [];

    /** @var array<string,Deferred<list<SysEx\BulkDumpBlock>>> */
    private array $deferredDump = [];

    /** @var array<string,Deferred<SysEx\ParameterChange>> */
    private array $deferredParam = [];

    public function __construct(
        private MidiDriver $midiDriver,
        private LoggerInterface $logger,
    ) {
        $this->midiDriver->setListener([$this, 'onMessage']);
    }

    /**
     * @return Promise<list<SysEx\BulkDumpBlock>>
     */
    public function requestDump(SysEx\DumpRequest $request): Promise
    {
        //TODO: race timeout
        $deferred = new Deferred();
        $this->deferredDump[$request->getAddress()->toBinaryString()] = $deferred;

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
            $promises[] = $this->midiDriver->send((string)$block->toSysEx());
        }

        return Promise\all($promises);
    }

    /**
     * @return Promise<SysEx\ParameterChange>
     */
    public function requestParameter(SysEx\ParameterRequest $request): Promise
    {
        //TODO: race timeout
        $deferred = new Deferred();
        $this->deferredParam[$request->getAddress()->toBinaryString()] = $deferred;

        $this->midiDriver->send((string)$request->toSysex());

        return $deferred->promise();
    }

    public function sendParameter(SysEx\ParameterChange $change): void
    {
        $this->midiDriver->send((string)$change->toSysEx());
    }

    public function onMessage(string $message): bool
    {
        $sysex = SysEx::fromBinaryString($message);
        if ($sysex === null) {
            return false;
        }

        switch ($sysex->getDeviceNumber()) {
            case SysEx\BulkDumpBlock::DEVICE_NUMBER:
                $this->handleBulkDumpBlock(SysEx\BulkDumpBlock::fromSysEx($sysex));
                break;
            case SysEx\ParameterChange::DEVICE_NUMBER:
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
                if (isset($this->deferredDump[$block->getAddress()->toBinaryString()])) {
                    $this->blocksBuffer = [$block];
                } else {
                    $this->logger->warning('Received unknown HeaderBlock');
                }
            } else {
                $this->logger->warning('Received BulkDumpBlock without current dump');
            }
        } else {
            if ($block->isFooterBlock()) {
                $headerAddress = $this->blocksBuffer[0]->getAddress();
                $footerAddress = $block->getAddress();
                if($headerAddress->m() === $footerAddress->m() && $headerAddress->l() === $footerAddress->l()) {
                    $this->blocksBuffer[] = $block;

                    $deferredKey = $headerAddress->toBinaryString();
                    $deferred = $this->deferredDump[$deferredKey];
                    $buffer = $this->blocksBuffer;
                    unset($this->deferredDump[$deferredKey]);
                    $this->blocksBuffer = [];

                    $deferred->resolve($buffer);
                } else {
                    $this->logger->warning('Received unknown FooterBlock');
                }
            } else {
                if ($block->isHeaderBlock()) {
                    $this->logger->warning('Received BulkDumpBlock while current dump is incomplete... Resetting');
                    if (isset($this->deferredDump[$block->getAddress()->toBinaryString()])) {
                        $this->blocksBuffer = [$block];
                    } else {
                        $this->logger->warning('Received unknown HeaderBlock');
                    }
                } else {
                    $this->blocksBuffer[] = $block;
                }
            }
        }
    }

    private function handleParameterChange(SysEx\ParameterChange $change): void
    {
        $deferredKey = $change->getAddress()->toBinaryString();
        $deferred = $this->deferredParam[$deferredKey];
        unset($this->deferredParam[$deferredKey]);
        $deferred->resolve($change);
    }
}
