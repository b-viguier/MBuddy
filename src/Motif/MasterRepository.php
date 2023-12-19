<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class MasterRepository
{
    public function __construct(
        private MidiDriver $midiDriver,
    ) {
    }

    public function get(MasterId $id, callable $onResponse): void
    {
        $dumpRequest = Master::getDumpRequest($id);
        $address = $dumpRequest->getAddress();

        $buffer = [];
        $listener = function (string $data) use ($onResponse, $address, &$buffer, &$listener): bool {
            if ($buffer === [] && !SysEx\BulkDumpBlock::matchBulkHeaderBlock($data, $address)) {
                return false;
            }

            $buffer[] = SysEx\BulkDumpBlock::fromBinaryString($data);
            if (count($buffer) === Master::DUMP_NB_BLOCKS) {
                $master = Master::fromBulkDumpBlocks(...$buffer);
                $this->midiDriver->removeListener($listener);
                $onResponse($master);
            }

            return true;
        };

        $this->midiDriver->setListener($listener);
        $this->midiDriver->send((string) $dumpRequest);
    }

    public function set(Master $master): void
    {
        foreach($master->getBulkDumpBlocks() as $block) {
            $this->midiDriver->send((string) $block);
        }
    }
}
