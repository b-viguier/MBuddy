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

    public function currentIdChanged(Id $id): Signal
    {
        return Signal::auto();
    }

    public function currentChanged(Preset $preset): Signal
    {
        return Signal::auto();
    }

    public function presetSaved(Preset $preset): Signal
    {
        return Signal::auto();
    }

    public function __construct(
        private Master\Repository $masterRepository,
    ) {
    }


    /**
     * @return Promise<Id>
     */
    public function currentId(): Promise
    {
        return call(function() {
            return Id::fromMasterId(yield $this->masterRepository->currentMasterId());
        });
    }

    /**
     * @return Promise<Preset>
     */
    public function current(): Promise
    {
        return call(function() {
            return $this->load(yield $this->currentId());
        });
    }

    /**
     * @return Promise<null>
     */
    public function setCurrentId(Id $id): Promise
    {
        return call(function() use ($id) {
            yield $this->masterRepository->setCurrentMasterId($id->masterId());

            $this->emit($this->currentIdChanged($id));
            $this->emit($this->currentChanged(yield $this->load($id)));
        });
    }

    /**
     * @return Promise<null>
     */
    public function save(Preset $preset): Promise
    {
        return $this->masterRepository->set($preset->master());
    }

    /**
     * @return Promise<Preset>
     */
    public function load(Id $id): Promise
    {
        return call(function() use ($id) {
            $master = yield $this->masterRepository->get($id->masterId());

            return new Preset($master);
        });
    }
}
