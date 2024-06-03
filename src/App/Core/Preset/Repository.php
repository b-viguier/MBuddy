<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core\Preset;

use Amp\Promise;
use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Motif\Master;
use Bveing\MBuddy\Siglot\Emitter;
use Bveing\MBuddy\Siglot\EmitterHelper;
use Bveing\MBuddy\Siglot\Signal;
use function Amp\call;

class Repository implements Emitter
{
    use EmitterHelper;

    public function currentChanged(Preset $preset): Signal
    {
        return Signal::auto();
    }

    public function presetChanged(Preset $preset): Signal
    {
        return Signal::auto();
    }


    public function __construct(
        private Master\Repository $masterRepository,
    ) {
    }


    /**
     * @return Promise<Preset>
     */
    public function current(): Promise
    {
        return call(function() {
            return new Preset(
                yield $this->masterRepository->get(
                    yield $this->masterRepository->currentMasterId(),
                ),
            );
        });
    }

    /**
     * @return Promise<null>
     */
    public function setCurrent(Master\Id $id): Promise
    {
        \assert(!$id->isEditBuffer());
        return call(function() use ($id) {
            yield $this->masterRepository->setCurrentMasterId($id);
            $master = yield $this->masterRepository->get($id);

            $this->emit($this->currentChanged(new Preset($master)));
        });
    }

    /**
     * @return Promise<void>
     */
    public function nextInBank(): Promise
    {
        return call(function() {
            $currentMasterId = yield $this->masterRepository->currentMasterId();
            $nextMasterId = $currentMasterId->next();

            if ($nextMasterId === null) {
                return;
            }

            yield $this->masterRepository->setCurrentMasterId($nextMasterId);
            $master = yield $this->masterRepository->get($nextMasterId);

            $this->emit($this->currentChanged(new Preset($master)));
        });
    }

    /**
     * @return Promise<void>
     */
    public function previousInBank(): Promise
    {
        return call(function() {
            $currentMasterId = yield $this->masterRepository->currentMasterId();
            $previousMasterId = $currentMasterId->previous();

            if ($previousMasterId === null) {
                return;
            }

            yield $this->masterRepository->setCurrentMasterId($previousMasterId);
            $master = yield $this->masterRepository->get($previousMasterId);

            $this->emit($this->currentChanged(new Preset($master)));
        });
    }
}
