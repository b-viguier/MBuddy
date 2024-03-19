<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\Master\Repository;

use Amp\Promise;
use Bveing\MBuddy\Motif\Master;
use Bveing\MBuddy\Motif\Master\Id;
use Bveing\MBuddy\Motif\SysEx;
use function Amp\call;

class Midi implements Master\Repository
{
    public function __construct(
        private SysEx\Client $sysExClient,
    ) {
    }

    public function get(Master\Id $id): Promise
    {
        return call(
            function(Master\Id $id) {
                $blocks = yield $this->sysExClient->requestDump(Master::dumpRequest($id));

                if ($blocks === null || \count($blocks) !== Master::DUMP_NB_BLOCKS) {
                    return null;
                }

                return Master::fromBulkDumpBlocks(...$blocks);
            },
            $id,
        );
    }

    public function set(Master $master): Promise
    {
        return call(function() use ($master) {
            yield $this->sysExClient->sendDump($master->toBulkDumpBlocks());
        });
    }

    public function currentMasterId(): Promise
    {
        return call(function() {
            $parameterChange = yield $this->sysExClient->requestParameter(
                new Sysex\ParameterRequest(new SysEx\Address(0x0A, 0x00, 0x00)),
            );

            if ($parameterChange === null) {
                return null;
            }

            return Id::fromInt($parameterChange->data()[0]);
        });
    }

    public function setCurrentMasterId(Master\Id $masterId): Promise
    {
        \assert(!$masterId->isEditBuffer());

        return $this->sysExClient->sendParameter(
            SysEx\ParameterChange::create(
                new SysEx\Address(0x0A, 0x00, 0x00),
                [$masterId->toInt()],
            ),
        );
    }
}
