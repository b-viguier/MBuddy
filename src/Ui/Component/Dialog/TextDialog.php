<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Ui\Component\Dialog;

use Amp\Promise;
use Bveing\MBuddy\Ui\Component\Dialog;
use Bveing\MBuddy\Ui\Component\TextEdit;
use function Amp\call;

class TextDialog extends Dialog
{
    public static function create(): self
    {
        return new self(
            textEdit: TextEdit::create(),
        );
    }

    /**
     * @return Promise<?string>
     */
    public function askText(string $title, string $current = ''): Promise
    {
        return call(function() use ($title, $current) {
            $this->textEdit->setText($current);
            $result = yield $this->run(
                title: $title,
                acceptText: 'Save',
                cancelText: 'Cancel',
            );

            return $result ? $this->textEdit->text() : null;
        });
    }

    private function __construct(
        private TextEdit $textEdit,
    ) {
        parent::__construct($this->textEdit);
    }
}
