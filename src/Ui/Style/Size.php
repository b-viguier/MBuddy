<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Style;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self SMALL()
 * @method static self MEDIUM()
 * @method static self LARGE()
 */
class Size extends Enum
{
    public function prefixed(string $prefix): string
    {
        return match($this->value) {
            self::MEDIUM => '',
            default => $prefix.$this->value,
        };
    }
    private const SMALL = 'sm';

    private const MEDIUM = 'md';

    private const LARGE = 'lg';

}
