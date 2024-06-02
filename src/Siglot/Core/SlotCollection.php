<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot\Core;

class SlotCollection
{
    /** @var \WeakMap<object, \ArrayObject<string,SlotMethod>> */
    private \WeakMap $slotInstances;

    public function __construct()
    {
        $this->slotInstances = new \WeakMap();
    }

    public function add(SlotMethod $slotMethod): void
    {
        \assert($slotMethod->isValid());
        $slotInstance = $this->slotInstances[$slotMethod->object()] ?? $this->slotInstances[$slotMethod->object()] = new \ArrayObject();

        $slotInstance[$slotMethod->name()] = $slotMethod;
    }

    public function remove(SlotMethod $slotMethod): void
    {
        \assert($slotMethod->isValid());
        unset($this->slotInstances[$slotMethod->object()][$slotMethod->name()]);
    }

    public function invoke(array $args): void
    {
        foreach ($this->slotInstances as $slotInstance) {
            foreach ($slotInstance as $slotMethod) {
                $slotMethod->invoke($args);
            }
        }
    }
}
