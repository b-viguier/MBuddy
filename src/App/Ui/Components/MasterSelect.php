<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui\Components;

use Bveing\MBuddy\App\Motif\Master\NameRegistry;
use Bveing\MBuddy\Motif\Master;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class MasterSelect
{
    public ?Master\Id $selected = null;

    public function __construct(
        private NameRegistry $nameRegistry,
    ) {
    }

    /**
     * @return iterable<int, string>
     */
    public function getMasters(): iterable
    {
        yield from $this->nameRegistry->all();
    }
}
