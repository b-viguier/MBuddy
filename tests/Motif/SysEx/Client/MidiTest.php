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

            $this->assertFalse($isSent);
            $message = $driver->popSentMessage();
            $this->assertTrue($isSent);

            $this->assertSame((string)$parameterChange->toSysex(), $message);
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
            $this->assertFalse($isSent);
            $messages[] = $driver->popSentMessage();
            $this->assertFalse($isSent);
            $messages[] = $driver->popSentMessage();
            $this->assertTrue($isSent);

            $this->assertSame((string)$dumpRequests[0]->toSysex(), $messages[0]);
            $this->assertSame((string)$dumpRequests[1]->toSysex(), $messages[1]);
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
            $this->assertSame((string)$parameterRequest->toSysex(), $message);
            $this->assertFalse($isFinished);

            // unrelated ParameterChange must be ignored
            $driver->pushReceivedMessage((string)$unrelatedParameterChange->toSysex());
            $this->assertFalse($isFinished);

            // ParameterChange with same address
            $driver->pushReceivedMessage((string)$parameterChange->toSysex());
            $this->assertTrue($isFinished);
            $result = yield $promiseRequest;

            $this->assertInstanceOf(SysEx\ParameterChange::class, $result);
            $this->assertSame($data, $result->data());
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
            $this->assertNull($result);
        });
    }

    public function testRequestDump(): void
    {
        Loop::run(function() {
            $driver = new Infrastructure\Motif\MidiDriver\InMemory();
            $client = new SysEx\Client\Midi($driver, new Infrastructure\ConsoleLogger(), 0.1);
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
            $this->assertSame((string)$dumpRequest->toSysex(), $message);
            $this->assertFalse($isFinished);

            $driver->pushReceivedMessage((string)$headerBlock->toSysex());
            $this->assertFalse($isFinished);
            $driver->pushReceivedMessage((string)$dataBlock->toSysex());
            $this->assertFalse($isFinished);
            $driver->pushReceivedMessage((string)$footerBlock->toSysex());
            $this->assertTrue($isFinished);

            $result = yield $promiseRequest;
            $this->assertIsArray($result);
            $this->assertCount(3, $result);

            $this->assertTrue($result[0]->isHeaderBlock());
            $this->assertEquals($headerBlock->address(), $result[0]->address());

            $this->assertTrue($result[2]->isFooterBlock());
            $this->assertEquals($footerBlock->address(), $result[2]->address());

            $this->assertEquals($dataBlock->address(), $result[1]->address());
            $this->assertSame($dataBlock->data(), $result[1]->data());
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
            $this->assertSame((string)$dumpRequest->toSysex(), $message);
            $this->assertFalse($isFinished);

            // Starting with non header block does nothing
            $driver->pushReceivedMessage((string)$dataBlock->toSysex());
            $driver->pushReceivedMessage((string)$footerBlock->toSysex());
            $this->assertFalse($isFinished);

            // When a new header arrive, current buffer is discarded
            $driver->pushReceivedMessage((string)$headerBlock->toSysex());
            $driver->pushReceivedMessage((string)$dataBlock->toSysex());
            $driver->pushReceivedMessage((string)$dataBlock->toSysex());
            $driver->pushReceivedMessage((string)$dataBlock->toSysex());
            $driver->pushReceivedMessage((string)$dataBlock->toSysex());
            $this->assertFalse($isFinished);
            $driver->pushReceivedMessage((string)$headerBlock->toSysex());
            $driver->pushReceivedMessage((string)$dataBlock->toSysex());
            $this->assertFalse($isFinished);

            // Invalid Footer are ignored
            $driver->pushReceivedMessage((string)$invalidFooterBlock->toSysex());
            $this->assertFalse($isFinished);
            $driver->pushReceivedMessage((string)$footerBlock->toSysex());
            $this->assertTrue($isFinished);

            $result = yield $promiseRequest;
            $this->assertIsArray($result);
            $this->assertCount(3, $result);

            $this->assertTrue($result[0]->isHeaderBlock());
            $this->assertEquals($headerBlock->address(), $result[0]->address());

            $this->assertTrue($result[2]->isFooterBlock());
            $this->assertEquals($footerBlock->address(), $result[2]->address());

            $this->assertEquals($dataBlock->address(), $result[1]->address());
            $this->assertSame($dataBlock->data(), $result[1]->data());
        });
    }
}
