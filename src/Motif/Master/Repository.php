<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\Master;

use Amp\Promise;
use Bveing\MBuddy\Motif\Master;
use Bveing\MBuddy\Motif\SysEx;
use Bveing\MBuddy\Motif\SysEx\ParameterRequest;
use Bveing\MBuddy\Motif\SysExClient;
use function Amp\call;

class Repository
{
    public function __construct(
        private SysExClient $sysExClient,
    ) {
    }

    /**
     * @return Promise<Master|null>
     */
    public function get(Master\Id $id): Promise
    {
        return call(
            function(Master\Id $id) {
                $blocks = yield $this->sysExClient->requestDump(Master::dumpRequest($id));

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
        return $this->sysExClient->sendDump($master->toBulkDumpBlocks());
    }

    /**
     * @return Promise<Master\Id>
     */
    public function currentMasterId(): Promise
    {
        return call(function() {
            $parameterChange = yield $this->sysExClient->requestParameter(
                new ParameterRequest(new SysEx\Address(0x0A, 0x00, 0x00)),
            );

            if ($parameterChange === null) {
                return null;
            }

            return Id::fromInt($parameterChange->data()[0]);
        });
    }

    /**
     * @return Promise<int>
     */
    public function setCurrentMasterId(Master\Id $masterId): Promise
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
    public function currentName(): Promise
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
                    $name .= chr($param->data()[0]);
                }
            }

            return $name;
        });
    }
}
