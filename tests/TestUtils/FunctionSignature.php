<?php

namespace bviguier\tests\MBuddy\TestUtils;

use PHPUnit\Framework\TestCase;

trait FunctionSignature
{
    static public function assertSameSignature(callable $expected, callable $actual): void
    {
        TestCase::assertSame(
            $expectedSignature = self::callableToString($expected),
            $actualSignature = self::callableToString($actual),
            "Signature ($actualSignature) doesn't match excepted ($expectedSignature)."
        );
    }

    static private function callableToString(callable $callable): string
    {
        return join(',', array_map(
            fn(\ReflectionParameter $param) => $param->getType()->getName(),
            (new \ReflectionFunction(\Closure::fromCallable($callable)))->getParameters()
        ));
    }
}
