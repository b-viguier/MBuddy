<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\SubTemplate;
use Bveing\MBuddy\Ui\Template;

class Html implements Component
{
    use Trait\AutoId;
    use Trait\Refreshable;
    use EmitterHelper;

    public static function create(): self
    {
        return (new self(Template::createEmpty()))->setHtml('');
    }

    public function setHtml(string $html): self
    {
        $this->template = Template::create(
            <<<HTML
            <span id="{{ id }}">
                {{ html }}
            </span>
            HTML,
            id: $this->id(),
            html: $html,
        );
        $this->refresh();

        return $this;
    }

    public function setTemplate(SubTemplate $subTemplate): self
    {
        $this->template = Template::create(
            <<<HTML
            <span id="{{ id }}">
                {{ sub }}
            </span>
            HTML,
            id: $this->id(),
            sub: $subTemplate,
        );
        $this->refresh();

        return $this;
    }

    public function template(): Template
    {
        return $this->template;
    }

    public function __construct(
        private Template $template,
    ) {
    }
}
