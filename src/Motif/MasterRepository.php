<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

use Amp\Promise;
use Bveing\MBuddy\Motif\SysEx\ParameterRequest;

use function Amp\call;

class MasterRepository
{
    public function __construct(
        private SysExClient $sysExClient,
    ) {
    }

    /**
     * @return Promise<Master|null>
     */
    public function get(MasterId $id): Promise
    {
        return call(
            function(MasterId $id) {
                $blocks = yield $this->sysExClient->requestDump(Master::getDumpRequest($id));

                if ($blocks === null || count($blocks) !== Master::DUMP_NB_BLOCKS) {
                    return null;
                }

                return Master::fromBulkDumpBlocks(...$blocks);
            },
            $id,
        );
    }

    /**
     * @return Promise<array<array-key,int>>
     */
    public function set(Master $master): Promise
    {
        return $this->sysExClient->sendDump($master->getBulkDumpBlocks());
    }

    /**
     * @return Promise<MasterId>
     */
    public function getCurrentMasterId(): Promise
    {
        return call(function() {
            $parameterChange = yield $this->sysExClient->requestParameter(
                new ParameterRequest(new SysEx\Address(0x0A, 0x00, 0x00)),
            );

            if ($parameterChange === null) {
                return null;
            }

            return MasterId::fromInt($parameterChange->getData()[0]);
        });
    }

    /**
     * @return Promise<int>
     */
    public function setCurrentMasterId(MasterId $masterId): Promise
    {
        assert(!$masterId->isEditBuffer());

        return $this->sysExClient->sendParameter(
            SysEx\ParameterChange::create(
                new SysEx\Address(0x0A, 0x00, 0x00),
                [$masterId->toInt()],
            ),
        );
    }

    /**
     * @return Promise<string>
     */
    public function getCurrentName(): Promise
    {
        return call(function() {

            $promises = [];
            for ($i = 0; $i < 20; $i++) {
                $promises[] = $this->sysExClient->requestParameter(
                    new ParameterRequest(new SysEx\Address(0x33, 0x00, $i)),
                );
            }
            $params = yield Promise\all($promises);

            $name = '';
            foreach ($params as $param) {
                if ($param === null) {
                    $name .= ' ';
                } else {
                    $name .= chr($param->getData()[0]);
                }
            }

            return $name;
        });
    }
}
