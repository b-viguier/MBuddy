<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\Master;

use Amp\Promise;
use Bveing\MBuddy\Motif\Master;

interface Repository
{
    /**
     * @return Promise<Master|null>
     */
    public function get(Master\Id $id): Promise;

    /**
     * @return Promise<null>
     */
    public function set(Master $master): Promise;

    /**
     * @return Promise<Master\Id>
     */
    public function currentMasterId(): Promise;

    /**
     * @return Promise<int>
     */
    public function setCurrentMasterId(Master\Id $id): Promise;
}
