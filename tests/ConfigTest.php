<?php

namespace bviguier\tests\MBuddy;

use bviguier\MBuddy\Config;
use bviguier\MBuddy\Device;
use bviguier\MBuddy\MidiSyxBank;
use bviguier\RtMidi\MidiBrowser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConfigTest extends TestCase
{
    public function testMidiBrowser(): void
    {
        $config = new Config([]);

        $this->assertInstanceOf(MidiBrowser::class, $browser = $config->midiBrowser());
        $this->assertSame($browser, $config->midiBrowser());
    }

    public function testLoggers(): void
    {
        $config = new Config([]);

        $this->assertInstanceOf(LoggerInterface::class, $logger = $config->logger('main'));
        $this->assertSame($logger, $config->logger('main'));
        $this->assertNotSame($logger, $config->logger('other'));
    }

    public function testMidiSysxBank(): void
    {
        $config = new Config([]);

        $this->assertInstanceOf(MidiSyxBank::class, $bank = $config->midiSyxBank());
        $this->assertSame($bank, $config->midiSyxBank());
    }

    public function testDeviceImpulse(): void
    {
        $config = new Config([
            Config::IMPULSE_IN => 'deviceInput',
            Config::IMPULSE_OUT => 'deviceOutput',
        ]);
        $browser = $config->midiBrowser();
        $input = $browser->openVirtualInput('deviceOutput');
        $output = $browser->openVirtualOutput('deviceInput');

        $this->assertInstanceOf(Device\Impulse::class, $device = $config->deviceImpulse());
        $this->assertSame($device, $config->deviceImpulse());
    }

    public function testDeviceImpulseMissingParameter(): void
    {
        $config = new Config([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing '".Config::IMPULSE_IN."' config parameter.");
        $config->deviceImpulse();
    }

    public function testDevicePa50(): void
    {
        $config = new Config([
            Config::PA50_IN => 'deviceInput',
            Config::PA50_OUT => 'deviceOutput',
        ]);
        $browser = $config->midiBrowser();
        $input = $browser->openVirtualInput('deviceOutput');
        $output = $browser->openVirtualOutput('deviceInput');

        $this->assertInstanceOf(Device\Pa50::class, $device = $config->devicePa50());
        $this->assertSame($device, $config->devicePa50());
    }

    public function testDevicePa50MissingParameter(): void
    {
        $config = new Config([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing '".Config::PA50_IN."' config parameter.");
        $config->devicePa50();
    }

    public function testInvalidConfiguration(): void
    {
        // @phpstan-ignore-next-line
        $config = new Config([
            Config::IMPULSE_IN => 1234,
        ]);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage("Config parameter '".Config::IMPULSE_IN."' must be a string");
        $config->deviceImpulse();
    }
}
