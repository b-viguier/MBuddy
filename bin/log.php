<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Amp\Loop;
use Amp\Socket\DatagramSocket;

Loop::run(static function() {
    $datagram = DatagramSocket::bind('udp://0.0.0.0:8484');

    echo "Listening logs on {$datagram->getAddress()}" . \PHP_EOL;

    while ([$address, $data] = yield $datagram->receive()) {
        echo $data;
    }
});
