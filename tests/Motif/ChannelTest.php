<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Motif;

use Bveing\MBuddy\Motif\Channel;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    public function testGetValues(): void
    {
        $channel = Channel::fromMidiByte(1);
        $this->assertSame(1, $channel->toMidiByte());
        $this->assertSame("2", $channel->toHumanReadable());
        $this->assertSame("2", (string) $channel);
    }
}
