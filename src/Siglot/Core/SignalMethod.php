<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Siglot\Core;

use Bveing\MBuddy\Siglot\Emitter;
use Bveing\MBuddy\Siglot\Signal;

class SignalMethod
{
    public static function fromClosure(\Closure $closure): self
    {
        $reflection = new \ReflectionFunction($closure);
        $object = $reflection->getClosureThis();

        if ($object === null) {
            throw new \Error("Closure is not bound to an object");
        }
        \assert($object instanceof Emitter);

        $methodName = $reflection->getName();
        $reflection = new \ReflectionMethod($object, $methodName);
        \assert(($returnType = $reflection->getReturnType()) instanceof \ReflectionNamedType);
        \assert($returnType->getName() === Signal::class);

        return new self(
            $methodName,
            $object,
            fn() => $this->$methodName(...\func_get_args()), // @phpstan-ignore-line
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function object(): Emitter
    {
        \assert($this->object->get() !== null);

        return $this->object->get();
    }

    public function isValid(): bool
    {
        return $this->object->get() !== null;
    }

    /**
     * @param mixed[] $args
     */
    public function invoke(array $args): Signal
    {
        $instance = $this->object->get();
        \assert($instance !== null);

        $callable = $this->function->bindTo($instance, $instance::class);

        return \call_user_func_array($callable, $args); // @phpstan-ignore-line
    }
    /** @var \WeakReference<Emitter> */
    private \WeakReference $object;

    /**
     * @param \Closure(mixed ...$params):Signal $function
     */
    private function __construct(
        private string $name,
        Emitter $object,
        private \Closure $function,
    ) {
        $this->object = \WeakReference::create($object);

        \assert(($reflection = new \ReflectionFunction($function))->getClosureThis() === null);
    }
}
