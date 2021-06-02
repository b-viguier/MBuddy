<?php

namespace bviguier\MBuddy;

interface Device
{
    /**
     * @param int $limit Max number of events to process
     *
     * @return int Actual number of processed events
     */
    public function process(int $limit): int;
}
