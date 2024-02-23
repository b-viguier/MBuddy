<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Ui\Component;

class Compound implements Component
{
    use Trait\NonModifiable;
    use Trait\AutoId;

    /** @var list<Component> */
    private array $htmlComponents;

    public function __construct(
        Component ...$htmlComponents,
    ) {
        $this->htmlComponents = array_values($htmlComponents);
    }
    public function render(): string
    {
        $html = '';
        foreach ($this->htmlComponents as $htmlComponent) {
            $html .= $htmlComponent->render();
        }

        return $html;
    }

    public function getChildren(): iterable
    {
        return $this->htmlComponents;
    }
}
