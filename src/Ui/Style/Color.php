<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Style;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 */
final class Color extends Enum
{
    private const PRIMARY = 'primary';
    private const SECONDARY = 'secondary';
    private const SUCCESS = 'success';
    private const DANGER = 'danger';
    private const WARNING = 'warning';
    private const INFO = 'info';
    private const LIGHT = 'light';
    private const DARK = 'dark';
    private const WHITE = 'white';
    private const BODY = 'body';
    private const MUTED = 'muted';
}
