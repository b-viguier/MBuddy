<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx\Client;

use Amp\Deferred;
use Amp\Promise;
use Bveing\MBuddy\Motif\MidiDriver;
use Bveing\MBuddy\Motif\SysEx;
use Psr\Log\LoggerInterface;
use function Amp\asyncCall;
use function Amp\delay;

class Midi implements Sysex\Client
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
        private float $timeoutInSeconds = 3.0,
    ) {
        \assert($timeoutInSeconds > 0, 'Timeout must be greater than 0');
        asyncCall(function() {
            while (true) {
                $message = yield $this->midiDriver->receive();
                if ($message === null) {
                    break;
                }

                $this->onMessage($message);
            }
        });
    }

    public function requestDump(SysEx\DumpRequest $request): Promise
    {
        $deferred = new Deferred();
        $deferredKey = $request->address()->toBinaryString();
        $this->deferredDump[$deferredKey] = $deferred;

        asyncCall(function() use ($request, $deferred, $deferredKey) {
            yield $this->midiDriver->send((string)$request->toSysex());

            yield delay(\intval($this->timeoutInSeconds * 1000));

            if (!isset($this->deferredDump[$deferredKey])) {
                return;
            }
            unset($this->deferredDump[$deferredKey]);
            $this->logger->warning('Timeout while waiting for dump');
            $deferred->resolve(null);
        });

        return $deferred->promise();
    }

    public function sendDump(iterable $blocks): Promise
    {
        $promises = [];
        foreach ($blocks as $block) {
            $promises[] = $this->midiDriver->send((string)$block->toSysEx());
        }

        return Promise\all($promises);
    }

    public function requestParameter(SysEx\ParameterRequest $request): Promise
    {
        $deferred = new Deferred();
        $deferredKey = $request->address()->toBinaryString();
        $this->deferredParam[$deferredKey] = $deferred;

        asyncCall(function() use ($request, $deferred, $deferredKey) {
            yield $this->midiDriver->send((string)$request->toSysex());

            yield delay(\intval($this->timeoutInSeconds * 1000));

            if (!isset($this->deferredParam[$deferredKey])) {
                return;
            }
            unset($this->deferredParam[$deferredKey]);
            $this->logger->warning('Timeout while waiting for parameter');
            $deferred->resolve(null);
        });

        return $deferred->promise();
    }

    public function sendParameter(SysEx\ParameterChange $change): Promise
    {
        return $this->midiDriver->send((string)$change->toSysEx());
    }

    private function onMessage(string $message): bool
    {
        $sysex = SysEx::fromBinaryString($message);
        if ($sysex === null) {
            return false;
        }

        try {
            switch ($sysex->deviceNumber()) {
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
        } catch (\Throwable $exception) {
            $this->logger->error('Error while handling SysEx message', ['message' => $exception->getMessage()]);

            return false;
        }
    }

    private function handleBulkDumpBlock(SysEx\BulkDumpBlock $block): void
    {
        if (!$this->blocksBuffer) {
            if ($block->isHeaderBlock()) {
                if (isset($this->deferredDump[$block->address()->toBinaryString()])) {
                    $this->blocksBuffer = [$block];
                } else {
                    $this->logger->warning('Received unknown HeaderBlock');
                }
            } else {
                $this->logger->warning('Received BulkDumpBlock without current dump');
            }
        } else {
            if ($block->isFooterBlock()) {
                $headerAddress = $this->blocksBuffer[0]->address();
                $footerAddress = $block->address();
                if ($headerAddress->m() === $footerAddress->m() && $headerAddress->l() === $footerAddress->l()) {
                    $deferredKey = $headerAddress->toBinaryString();
                    if (!isset($this->deferredDump[$deferredKey])) {
                        $this->logger->warning('Received FooterBlock but job has been cancelled');

                        return;
                    }
                    $deferred = $this->deferredDump[$deferredKey];

                    $this->blocksBuffer[] = $block;
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
                    if (isset($this->deferredDump[$block->address()->toBinaryString()])) {
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
        $deferredKey = $change->address()->toBinaryString();
        if (!isset($this->deferredParam[$deferredKey])) {
            $this->logger->warning('Received ParameterChange but job has been cancelled');

            return;
        }
        $deferred = $this->deferredParam[$deferredKey];
        unset($this->deferredParam[$deferredKey]);
        $deferred->resolve($change);
    }
}
