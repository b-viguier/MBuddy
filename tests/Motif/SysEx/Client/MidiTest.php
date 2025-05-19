<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif\SysEx\Client;

use Amp\Loop;
use Bveing\MBuddy\Infrastructure;
use Bveing\MBuddy\Motif\SysEx;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class MidiTest extends TestCase
{
    public function testSendParameter(): void
    {
        Loop::run(function() {
            $driver = new Infrastructure\Motif\MidiDriver\InMemory();
            $client = new SysEx\Client\Midi($driver, new NullLogger(), 0.1);


            $parameterChange = Sysex\ParameterChange::create(
                address: new Sysex\Address(0x01, 0x02, 0x03),
                data: [0x04, 0x05, 0x06],
            );

            $isSent = false;
            $client->sendParameter($parameterChange)->onResolve(function() use (&$isSent) {
                $isSent = true;
            });

            self::assertFalse($isSent);
            $message = $driver->popSentMessage();
            self::assertTrue($isSent);

            self::assertSame((string)$parameterChange->toSysEx(), $message);
        });
    }

    public function testSendDump(): void
    {
        Loop::run(function() {
            $driver = new Infrastructure\Motif\MidiDriver\InMemory();
            $client = new SysEx\Client\Midi($driver, new NullLogger(), 0.1);

            $dumpRequests = [
                SysEx\BulkDumpBlock::create(
                    byteCount: 3,
                    address: new SysEx\Address(0x0A, 0x0B, 0x0C),
                    data: [1,2,3],
                ),
                SysEx\BulkDumpBlock::create(
                    byteCount: 2,
                    address: new SysEx\Address(0x01, 0x02, 0x03),
                    data: [4,5],
                ),
            ];

            $isSent = false;
            $client->sendDump($dumpRequests)->onResolve(function() use (&$isSent) {
                $isSent = true;
            });

            $messages = [];
            self::assertFalse($isSent);
            $messages[] = $driver->popSentMessage();
            self::assertFalse($isSent);
            $messages[] = $driver->popSentMessage();
            self::assertTrue($isSent);

            self::assertSame((string)$dumpRequests[0]->toSysEx(), $messages[0]);
            self::assertSame((string)$dumpRequests[1]->toSysEx(), $messages[1]);
        });
    }

    public function testRequestParameter(): void
    {
        Loop::run(function() {
            $driver = new Infrastructure\Motif\MidiDriver\InMemory();
            $client = new SysEx\Client\Midi($driver, new NullLogger(), 0.1);
            $address = new SysEx\Address(0x01, 0x02, 0x03);
            $parameterRequest = new SysEx\ParameterRequest(
                $address,
            );
            $data = [0x04, 0x05, 0x06];
            $parameterChange = Sysex\ParameterChange::create($address, $data);
            $unrelatedParameterChange = Sysex\ParameterChange::create(
                new SysEx\Address(0x01, 0x02, 0x4),
                [42],
            );

            $isFinished = false;
            $promiseRequest = $client->requestParameter($parameterRequest);
            $promiseRequest->onResolve(function() use (&$isFinished) {
                $isFinished = true;
            });
            $message = $driver->popSentMessage();
            self::assertSame((string)$parameterRequest->toSysEx(), $message);
            self::assertFalse($isFinished);

            // unrelated ParameterChange must be ignored
            $driver->pushReceivedMessage((string)$unrelatedParameterChange->toSysEx());
            self::assertFalse($isFinished);

            // ParameterChange with same address
            $driver->pushReceivedMessage((string)$parameterChange->toSysEx());
            self::assertTrue($isFinished);
            $result = yield $promiseRequest;

            self::assertInstanceOf(SysEx\ParameterChange::class, $result);
            self::assertSame($data, $result->data());
        });
    }

    public function testRequestParameterTimeout(): void
    {
        Loop::run(function() {
            $driver = new Infrastructure\Motif\MidiDriver\InMemory();
            $client = new SysEx\Client\Midi($driver, new NullLogger(), 0.001);

            $promiseRequest = $client->requestParameter(new SysEx\ParameterRequest(
                new SysEx\Address(0x01, 0x02, 0x03),
            ));

            $driver->popSentMessage();
            // Don't send anything back
            $result = yield $promiseRequest;
            self::assertNull($result);
        });
    }

    public function testRequestDump(): void
    {
        Loop::run(function() {
            $driver = new Infrastructure\Motif\MidiDriver\InMemory();
            $client = new SysEx\Client\Midi($driver, new NullLogger(), 0.1);
            $addressM = 0x02;
            $addressL = 0x03;
            $data = [0x04, 0x05, 0x06];
            $headerBlock = SysEx\BulkDumpBlock::createHeaderBlock($addressM, $addressL);
            $footerBlock = SysEx\BulkDumpBlock::createFooterBlock($addressM, $addressL);
            $dataBlock = SysEx\BulkDumpBlock::create(
                byteCount: 3,
                address: new SysEx\Address(0x0A, 0x0B, 0x0C),
                data: $data,
            );
            $dumpRequest = new SysEx\DumpRequest($headerBlock->address());

            $isFinished = false;
            $promiseRequest = $client->requestDump($dumpRequest);
            $promiseRequest->onResolve(function() use (&$isFinished) {
                $isFinished = true;
            });

            $message = $driver->popSentMessage();
            self::assertSame((string)$dumpRequest->toSysEx(), $message);
            self::assertFalse($isFinished);

            $driver->pushReceivedMessage((string)$headerBlock->toSysEx());
            self::assertFalse($isFinished);
            $driver->pushReceivedMessage((string)$dataBlock->toSysEx());
            self::assertFalse($isFinished);
            $driver->pushReceivedMessage((string)$footerBlock->toSysEx());
            self::assertTrue($isFinished);

            $result = yield $promiseRequest;
            self::assertIsArray($result);
            self::assertCount(3, $result);

            self::assertTrue($result[0]->isHeaderBlock());
            self::assertEquals($headerBlock->address(), $result[0]->address());

            self::assertTrue($result[2]->isFooterBlock());
            self::assertEquals($footerBlock->address(), $result[2]->address());

            self::assertEquals($dataBlock->address(), $result[1]->address());
            self::assertSame($dataBlock->data(), $result[1]->data());
        });
    }

    public function testRequestDumpInterlacing(): void
    {
        Loop::run(function() {
            $driver = new Infrastructure\Motif\MidiDriver\InMemory();
            $client = new SysEx\Client\Midi($driver, new NullLogger(), 0.1);
            $addressM = 0x02;
            $addressL = 0x03;
            $data = [0x04, 0x05, 0x06];
            $headerBlock = SysEx\BulkDumpBlock::createHeaderBlock($addressM, $addressL);
            $footerBlock = SysEx\BulkDumpBlock::createFooterBlock($addressM, $addressL);
            $invalidFooterBlock = SysEx\BulkDumpBlock::createFooterBlock($addressM + 1, $addressL + 1);
            $dataBlock = SysEx\BulkDumpBlock::create(
                byteCount: 3,
                address: new SysEx\Address(0x0A, 0x0B, 0x0C),
                data: $data,
            );
            $dumpRequest = new SysEx\DumpRequest($headerBlock->address());

            $isFinished = false;
            $promiseRequest = $client->requestDump($dumpRequest);
            $promiseRequest->onResolve(function() use (&$isFinished) {
                $isFinished = true;
            });

            $message = $driver->popSentMessage();
            self::assertSame((string)$dumpRequest->toSysEx(), $message);
            self::assertFalse($isFinished);

            // Starting with non header block does nothing
            $driver->pushReceivedMessage((string)$dataBlock->toSysEx());
            $driver->pushReceivedMessage((string)$footerBlock->toSysEx());
            self::assertFalse($isFinished);

            // When a new header arrive, current buffer is discarded
            $driver->pushReceivedMessage((string)$headerBlock->toSysEx());
            $driver->pushReceivedMessage((string)$dataBlock->toSysEx());
            $driver->pushReceivedMessage((string)$dataBlock->toSysEx());
            $driver->pushReceivedMessage((string)$dataBlock->toSysEx());
            $driver->pushReceivedMessage((string)$dataBlock->toSysEx());
            self::assertFalse($isFinished);
            $driver->pushReceivedMessage((string)$headerBlock->toSysEx());
            $driver->pushReceivedMessage((string)$dataBlock->toSysEx());
            self::assertFalse($isFinished);

            // Invalid Footer are ignored
            $driver->pushReceivedMessage((string)$invalidFooterBlock->toSysEx());
            self::assertFalse($isFinished);
            $driver->pushReceivedMessage((string)$footerBlock->toSysEx());
            self::assertTrue($isFinished);

            $result = yield $promiseRequest;
            self::assertIsArray($result);
            self::assertCount(3, $result);

            self::assertTrue($result[0]->isHeaderBlock());
            self::assertEquals($headerBlock->address(), $result[0]->address());

            self::assertTrue($result[2]->isFooterBlock());
            self::assertEquals($footerBlock->address(), $result[2]->address());

            self::assertEquals($dataBlock->address(), $result[1]->address());
            self::assertSame($dataBlock->data(), $result[1]->data());
        });
    }
}
