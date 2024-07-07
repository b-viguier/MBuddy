<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component;

use Amp\Promise;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Siglot;
use Bveing\MBuddy\Siglot\Signal;
use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Id;
use Bveing\MBuddy\Ui\Style\Color;
use Bveing\MBuddy\Ui\SubTemplate;
use Bveing\MBuddy\Ui\Template;
use Bveing\MBuddy\Ui\Tool\AsyncBoolSlot;
use function Amp\call;

abstract class Dialog implements Component
{
    use EmitterHelper;

    public function id(): Id
    {
        return $this->modal->id();
    }

    public function modified(): Signal
    {
        return Signal::auto();
    }

    public function template(): Template
    {
        return $this->modal->template();
    }


    /**
     * @return Promise<bool>
     */
    protected function run(string $title, string $acceptText, string $cancelText): Promise
    {
        return call(function() use ($title, $acceptText, $cancelText) {
            $this->header->setHtml(
                \sprintf(
                    '<h5>%s</h5>',
                    $title,
                ),
            );
            $this->confirmButton->set(label: $acceptText);
            $this->cancelButton->set(label: $cancelText);

            $slot = new AsyncBoolSlot();
            Siglot::connect0(
                \Closure::fromCallable([$this->confirmButton, 'clicked']),
                \Closure::fromCallable([$slot, 'accept']),
            );
            Siglot::connect0(
                \Closure::fromCallable([$this->cancelButton, 'clicked']),
                \Closure::fromCallable([$slot, 'reject']),
            );

            $this->modal->show();
            $result = yield $slot->result();
            $this->modal->hide();

            return $result;
        });
    }

    protected function __construct(Component $body)
    {
        $this->confirmButton = Button::create()->set(
            color: Color::SUCCESS(),
        );
        $this->cancelButton = Button::create()->set(
            color: Color::DANGER(),
        );

        $this->modal = new Modal(
            content: $body,
            header: $this->header = Html::create(),
            footer: Html::create()->setTemplate(
                SubTemplate::create(
                    <<<HTML
                    {{ cancelButton }}
                    {{ confirmButton }}
                    HTML,
                    confirmButton: $this->confirmButton,
                    cancelButton: $this->cancelButton,
                )
            ),
        );

        Siglot::chain0(
            \Closure::fromCallable([$this->modal, 'modified']),
            \Closure::fromCallable([$this, 'modified']),
        );
    }

    private Modal $modal;
    private Html $header;
    private Button $confirmButton;
    private Button $cancelButton;
}
