<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Dialog;

use Amp\Promise;
use Bveing\MBuddy\Ui\Component\Dialog;
use Bveing\MBuddy\Ui\Component\Html;

class ConfirmDialog extends Dialog
{
    public static function create(): self
    {
        return new self(
            body: Html::create(),
        );
    }

    /**
     * @return Promise<bool>
     */
    public function ask(string $question, ?string $title = null): Promise
    {
        $this->body->setHtml("<p>{$question}</p>");

        return $this->run(
            title: $title ?? 'Confirm',
            acceptText: 'Confirm',
            cancelText: 'Cancel',
        );
    }

    private function __construct(
        private Html $body,
    ) {
        parent::__construct($body);
    }
}
