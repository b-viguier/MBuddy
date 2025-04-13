<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui\Components;

use Bveing\MBuddy\App\Core\Preset;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PresetCard
{
    public Preset $preset;

    public ?string $href = null;

    public ?string $icon = null;

    public function color(): string
    {
        return '#' . substr(md5($this->preset->id()->toString()), 0, 6);
    }
}