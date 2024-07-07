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

class ConfirmDialog implements Component
{
    use EmitterHelper;

    public static function create(): self
    {
        $confirmBtn = Button::create()->set(
            label: 'Confirm',
            color: Color::SUCCESS(),
        );
        $cancelBtn = Button::create()->set(
            label: 'Cancel',
            color: Color::DANGER(),
        );

        $header = Html::create();
        $body = Html::create();
        $footer = Html::create()->setTemplate(
            SubTemplate::create(
                <<<HTML
                    {{ cancelButton }}
                    {{ confirmButton }}
                HTML,
                confirmButton: $confirmBtn,
                cancelButton: $cancelBtn,
            )
        );
        return new self(
            modal: new Modal(content: $body, header: $header, footer: $footer),
            header: $header,
            body: $body,
            confirmButton: $confirmBtn,
            cancelButton: $cancelBtn,
        );
    }

    /**
     * @return Promise<bool>
     */
    public function ask(string $question, ?string $title = null): Promise
    {
        return call(function() use ($question, $title) {
            $this->header->setHtml(
                \sprintf(
                    '<h5>%s</h5>',
                    $title ?? 'Confirm',
                ),
            );
            $this->body->setHtml(
                \sprintf(
                    '<p>%s</p>',
                    $question,
                ),
            );

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

    private function __construct(
        private Modal $modal,
        private Html $header,
        private Html $body,
        private Button $confirmButton,
        private Button $cancelButton,
    ) {
        Siglot::chain0(
            \Closure::fromCallable([$this->modal, 'modified']),
            \Closure::fromCallable([$this, 'modified']),
        );
    }
}
