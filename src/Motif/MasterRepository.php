<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

use Amp\Promise;
use Bveing\MBuddy\Motif\SysEx\ParameterRequest;

use function Amp\call;

class MasterRepository
{
    public function __construct(
        private SysexManager $sysexManager,
    ) {
    }

    /**
     * @return Promise<Master>
     */
    public function get(MasterId $id): Promise
    {
        return call(
            function(MasterId $id) {
                $blocks = yield $this->sysexManager->requestDump(Master::getDumpRequest($id));

                if (count($blocks) !== Master::DUMP_NB_BLOCKS) {
                    throw new \RuntimeException('Invalid number of blocks');
                }

                return Master::fromBulkDumpBlocks(...$blocks);
            },
            $id
        );
    }

    /**
     * @return Promise<array<array-key,int>>
     */
    public function set(Master $master): Promise
    {
        return $this->sysexManager->sendDump($master->getBulkDumpBlocks());
    }

    /**
     * @return Promise<MasterId>
     */
    public function getCurrentMasterId(): Promise
    {
        return call(function() {
            $parameterChange = yield $this->sysexManager->requestParameter(
                new ParameterRequest(new SysEx\Address(0x0A, 0x00, 0x00))
            );

            return MasterId::fromInt($parameterChange->getData()[0]);
        });
    }
}
