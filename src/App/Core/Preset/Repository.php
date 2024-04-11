<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Core\Preset;

use Amp\Promise;
use Bveing\MBuddy\App\Core\Preset;
use Bveing\MBuddy\Core\Signal;
use Bveing\MBuddy\Core\Slot;
use Bveing\MBuddy\Motif\Master;
use function Amp\call;

class Repository
{
    public Slot\Slot0 $nextInBank;
    public Slot\Slot0 $previousInBank;
    /** @var Signal\Signal1<Preset> */
    public Signal\Signal1 $currentChanged;
    public Signal\Signal1 $presetChanged;


    public function __construct(
        private Master\Repository $masterRepository,
    ) {
        $this->nextInBank = new Slot\Slot0(fn() => $this->nextInBank());
        $this->previousInBank = new Slot\Slot0(fn() => $this->previousInBank());
        $this->currentChanged = new Signal\Signal1();
        $this->presetChanged = new Signal\Signal1();
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
        return call(function() use($id) {
            yield $this->masterRepository->setCurrentMasterId($id);
            $master = yield $this->masterRepository->get($id);

            $this->currentChanged->emit(new Preset($master));
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

            $this->currentChanged->emit(new Preset($master));
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

            $this->currentChanged->emit(new Preset($master));
        });
    }
}
