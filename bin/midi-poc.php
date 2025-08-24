<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function() {

    $driver = new \Bveing\MBuddy\Motif\MidiDriver\RateLimiter(
        \Bveing\MBuddy\Infrastructure\Motif\MidiDriver\Udp::create(
    'udp://0.0.0.0:8321',
            'udp://192.168.1.161:8123',
        ),
        0.1,
    );
    $logger = new \Symfony\Component\Console\Logger\ConsoleLogger(
        new \Symfony\Component\Console\Output\ConsoleOutput(),
    );
    $sysExClient = new \Bveing\MBuddy\Motif\SysEx\Client\ConcurrencyLimiter(
        new \Bveing\MBuddy\Motif\SysEx\Client\Midi($driver, $logger),
        5,
    );

    yield $driver->send(
        join('', array_map('chr', [
            0b10010000, // MIDI Channel 1, Note On
            60,        // Note number (Middle C)
            127,       // Velocity
        ])),
    );

    Amp\Loop::stop();
});

function displayMaster(
    \Bveing\MBuddy\Motif\Master\Repository $repository,
    \Bveing\MBuddy\Motif\Master\Id $masterId,
): \Generator {
    echo "Loading {$masterId->toInt()}...\n";
    try {
        $master = yield $repository->get($masterId);
        if ($master === null) {
            echo "{$masterId->toInt()}: not found\n";

            return;
        }
        echo "{$master->id()->toInt()}: >{$master->name()}<\n";
    } catch (\Throwable $e) {
        echo "Error: {$masterId->toInt()}\n";
        var_dump($e);
    }
}
