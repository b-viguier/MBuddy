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
        self::assertSame(1, $channel->toMidiByte());
        self::assertSame("2", $channel->toHumanReadable());
        self::assertSame("2", (string) $channel);
    }
}
