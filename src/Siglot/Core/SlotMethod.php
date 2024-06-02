<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot\Core;

use Bveing\MBuddy\Siglot\Emitter;
use Bveing\MBuddy\Siglot\Signal;

class SlotMethod
{
    /** @var \WeakReference<object> */
    private \WeakReference $object;

    private function __construct(
        private string $name,
        object $object,
        private \Closure $function,
        private bool $isSignal,
    ) {
        $this->object = \WeakReference::create($object);

        $reflection = new \ReflectionFunction($function);

        \assert($reflection->getClosureThis() === null);
    }


    static public function fromClosure(\Closure $closure): self
    {
        $reflection = new \ReflectionFunction($closure);
        $object = $reflection->getClosureThis();

        if ($object === null) {
            throw new \Error("Closure is not bound to an object");
        }

        $methodName = $reflection->getName();
        new \ReflectionMethod($object, $methodName);

        return new self(
            $methodName,
            $object,
            fn() => $this->$methodName(...func_get_args()),
            $object instanceof Emitter
                && ($returnType = $reflection->getReturnType()) instanceof \ReflectionNamedType
                && $returnType->getName() === Signal::class,
        );
    }

    static public function wrap(self $wrapped, \Closure $wrapper): self
    {
        \assert($wrapped->isSignal);

        return new self(
            $wrapped->name,
            $wrapped->object->get(),
            fn() => $wrapper($wrapped->invoke(func_get_args())->args()),
            false,
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function object(): object
    {
        \assert($this->object->get() !== null);

        return $this->object->get();
    }

    public function isValid(): bool
    {
        return $this->object->get() !== null;
    }

    public function isSignal(): bool
    {
        return $this->isSignal;
    }

    public function invoke(array $args): mixed
    {
        \assert($this->object->get() !== null);

        return \call_user_func_array($this->function->bindTo($this->object->get(), $this->object->get()::class), $args);
    }
}
