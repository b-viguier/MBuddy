<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function() {
    $browser = new \bviguier\RtMidi\MidiBrowser("/opt/homebrew/lib/librtmidi.dylib");
    $input = $browser->openInput("WIDI Jack Bluetooth");
    $output = $browser->openOutput("WIDI Jack Bluetooth");

    $logger = new \Bveing\MBuddy\Infrastructure\ConsoleLogger();

    $driver = new \Bveing\MBuddy\Motif\MidiDriver\RateLimiter(
        new \Bveing\MBuddy\Infrastructure\Motif\MidiDriver\RtMidi($input, $output),
        0.1,
    );
    $sysExClient = new \Bveing\MBuddy\Motif\SysExClient\ConcurrencyLimiter(
        new \Bveing\MBuddy\Motif\SysExClient\Midi($driver, $logger),
        5,
    );
    $repository = new \Bveing\MBuddy\Motif\MasterRepository($sysExClient);

    $promises = [];
    $countdown = 6;
    foreach (\Bveing\MBuddy\Motif\MasterId::getAll() as $masterId) {
        $promises[] = \Amp\call('displayMaster', $repository, $masterId);
        if (--$countdown === 0) {
            break;
        }
    }

    yield \Amp\Promise\all($promises);

    Amp\Loop::stop();
});

function displayMaster(
    \Bveing\MBuddy\Motif\MasterRepository $repository,
    \Bveing\MBuddy\Motif\MasterId $masterId,
): \Generator {
    echo "Loading {$masterId->toInt()}...\n";
    try {
        $master = yield $repository->get($masterId);
        if ($master === null) {
            echo "{$masterId->toInt()}: not found\n";

            return;
        }
        echo "{$master->getId()->toInt()}: >{$master->getName()}<\n";
    } catch (\Throwable $e) {
        echo "Error: {$masterId->toInt()}\n";
        var_dump($e);
    }
}
