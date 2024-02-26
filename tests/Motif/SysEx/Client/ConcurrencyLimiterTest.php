<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx\Client;

use Amp\Deferred;
use Amp\Loop;
use Amp\Success;
use Bveing\MBuddy\Motif\SysEx;
use PHPUnit\Framework\TestCase;

class ConcurrencyLimiterTest extends TestCase
{
    public function testSendDumpIsForwarded(): void
    {
        $blocks = [
            SysEx\BulkDumpBlock::createHeaderBlock(0, 0),
            SysEx\BulkDumpBlock::createFooterBlock(0, 0),
        ];
        $promise = new Success();
        $mock = $this->createMock(SysEx\Client::class);
        $mock
            ->expects($this->once())
            ->method('sendDump')
            ->with($blocks)
            ->willReturn($promise);

        $client = new SysEx\Client\ConcurrencyLimiter($mock, 1);
        $result = $client->sendDump($blocks);

        $this->assertSame($promise, $result);
    }

    public function testSendParameterIsForwarded(): void
    {
        $parameter = SysEx\ParameterChange::create(new SysEx\Address(0, 0, 0), [1]);
        $promise = new Success();
        $mock = $this->createMock(SysEx\Client::class);
        $mock
            ->expects($this->once())
            ->method('sendParameter')
            ->with($parameter)
            ->willReturn($promise);

        $client = new SysEx\Client\ConcurrencyLimiter($mock, 1);
        $result = $client->sendParameter($parameter);

        $this->assertSame($promise, $result);
    }

    public function testRequestsAreLimited(): void
    {
        Loop::run(function() {
            $deferred = [
                new Deferred(),
                new Deferred(),
                new Deferred(),
            ];
            $isStarted = [false, false, false];
            $isFinished = [false, false, false];

            $dumpRequest = new SysEx\DumpRequest(new SysEx\Address(0, 0, 0));
            $parameterRequest = new SysEx\ParameterRequest(new SysEx\Address(0, 0, 0));

            $mock = $this->createMock(SysEx\Client::class);
            $mock
                ->expects($this->exactly(2))
                ->method('requestDump')
                ->with($dumpRequest)
                ->willReturnCallback(function() use (&$isStarted, $deferred) {
                    static $index = 0;
                    \assert($index <= 2, 'Too many calls to sendDump');
                    $isStarted[$index] = true;
                    $promise = $deferred[$index]->promise();
                    $index += 2;

                    return $promise;
                });
            $mock
                ->expects($this->once())
                ->method('requestParameter')
                ->with($parameterRequest)
                ->willReturnCallback(function() use (&$isStarted, $deferred) {
                    $isStarted[1] = true;

                    return $deferred[1]->promise();
                });

            $client = new SysEx\Client\ConcurrencyLimiter($mock, 2);

            $client->requestDump($dumpRequest)->onResolve(function() use (&$isFinished) {
                $isFinished[0] = true;
            });
            $client->requestParameter($parameterRequest)->onResolve(function() use (&$isFinished) {
                $isFinished[1] = true;
            });
            $client->requestDump($dumpRequest)->onResolve(function() use (&$isFinished) {
                $isFinished[2] = true;
            });

            $this->assertSame([true, true, false], $isStarted);
            $this->assertSame([false, false, false], $isFinished);

            $deferred[0]->resolve();
            $this->assertSame([true, true, true], $isStarted);
            $this->assertSame([true, false, false], $isFinished);

            $deferred[1]->resolve();
            $this->assertSame([true, true, true], $isStarted);
            $this->assertSame([true, true, false], $isFinished);

            $deferred[2]->resolve();
            $this->assertSame([true, true, true], $isStarted);
            $this->assertSame([true, true, true], $isFinished);
        });
    }
}
