<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif\Master;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<int>
 */
final class Mode extends Enum
{
    private const VOICE = 0;
    private const PERFORMANCE = 1;
    private const PATTERN = 2;
    private const SONG = 3;
}
