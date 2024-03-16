<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Core;

class Signal extends Slot
{
    public function __construct()
    {
        parent::__construct(fn(mixed ...$args) => $this->_emit(...$args));
    }

    public function disconnect(Slot $slot): void
    {
        $this->weakSlots = \array_filter(
            $this->weakSlots,
            fn(\WeakReference $weakSlot) => $weakSlot->get() && $weakSlot->get() !== $slot
        );
    }

    protected function _connect(Slot $slot): void
    {
        $this->weakSlots[] = \WeakReference::create($slot);
    }

    protected function _emit(mixed ...$args): void
    {
        $allSlots = $this->weakSlots;
        $this->weakSlots = [];
        $newSlots = [];
        foreach ($allSlots as $weakSlot) {
            if ($weakSlot->get() === null) {
                continue;
            }
            $newSlots[] = $weakSlot;
            $weakSlot->get()->_call(...$args);
        }
        $this->weakSlots = $newSlots;
    }

    /**
     * @var list<\WeakReference<Slot>>
     */
    private array $weakSlots = [];
}
