<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests;

use Amp\Loop;
use Amp\Promise;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;

class GeckoServerExtension implements BeforeFirstTestHook, BeforeTestHook, AfterTestHook, AfterLastTestHook, AfterTestErrorHook, AfterTestFailureHook
{
    public static GeckoDriver $driver;
    public function __construct()
    {
        $host = \getenv('WEBDRIVER_HOST') ?: 'http://webdriver:4444';
        self::$driver = new GeckoDriver($host);
    }

    /**
     * @return Promise<null>
     */
    public static function navigateToHomePage(): Promise
    {
        return self::$driver->navigateTo("http://php:8383/");
    }

    public function executeAfterLastTest(): void
    {
        Loop::run(function() {
            yield self::$driver->stop();
        });
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        // TODO: Implement executeAfterTestError() method.
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        // TODO: Implement executeAfterTestFailure() method.
    }

    public function executeAfterTest(string $test, float $time): void
    {
        // TODO: Implement executeAfterTest() method.
    }

    public function executeBeforeFirstTest(): void
    {
        Loop::run(function() {
            yield self::$driver->start();
        });
    }

    public function executeBeforeTest(string $test): void
    {
        // TODO: Implement executeBeforeTest() method.
    }
}
