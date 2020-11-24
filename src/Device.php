<?php

namespace bviguier\MBuddy;

interface Device
{
    public function process(int $limit): int;
}
