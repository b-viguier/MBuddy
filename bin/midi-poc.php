<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function() {
    $browser = new \bviguier\RtMidi\MidiBrowser("/opt/homebrew/lib/librtmidi.dylib");
    $input = $browser->openInput("WIDI Jack Bluetooth");
    $output = $browser->openOutput("WIDI Jack Bluetooth");

    $logger = new \Bveing\MBuddy\Infrastructure\ConsoleLogger();

    $driver = new \Bveing\MBuddy\Infrastructure\Motif\MidiDriver\RtMidi($input, $output);
    $sysexManager = new \Bveing\MBuddy\Motif\SysexManager($driver, $logger);
    $repository = new \Bveing\MBuddy\Motif\MasterRepository($sysexManager);

    $master = yield $repository->get(\Bveing\MBuddy\Motif\MasterId::fromInt(0));

    $logger->info("Master: [{$master->getName()}]");
});
