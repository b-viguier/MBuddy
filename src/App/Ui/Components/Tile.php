<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Tile
{
    public string $title;

    public ?string $icon = null;

    public ?string $href = null;
}
