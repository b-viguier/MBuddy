<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\SysEx;

use Amp\Promise;
use Bveing\MBuddy\Motif\SysEx;

interface Client
{
    /**
     * @return Promise<list<SysEx\BulkDumpBlock>|null>
     */
    public function requestDump(SysEx\DumpRequest $request): Promise;

    /**
     * @param iterable<SysEx\BulkDumpBlock> $blocks
     * @return Promise<array<array-key,int>>
     */
    public function sendDump(iterable $blocks): Promise;

    /**
     * @return Promise<SysEx\ParameterChange|null>
     */
    public function requestParameter(SysEx\ParameterRequest $request): Promise;

    /**
     * @return Promise<int>
     */
    public function sendParameter(SysEx\ParameterChange $change): Promise;
}