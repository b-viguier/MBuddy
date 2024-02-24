<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\Master;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 */
final class KnobSliderFunction extends Enum
{
    private const TONE1 = 0;
    private const TONE2 = 1;
    private const ARP = 2;
    private const REV = 3;
    private const CHO = 4;
    private const PAN = 5;
    private const ZONE = 6;
}
