<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function() {
    $browser = new \bviguier\RtMidi\MidiBrowser("/opt/homebrew/lib/librtmidi.dylib");
    $input = $browser->openInput("WIDI Jack Bluetooth");
    $output = $browser->openOutput("WIDI Jack Bluetooth");

    $logger = new \Bveing\MBuddy\Infrastructure\ConsoleLogger();

    $driver = new \Bveing\MBuddy\Infrastructure\Motif\MidiDriver\RtMidi($input, $output);
    $sysExClient = new \Bveing\MBuddy\Motif\SysExClient($driver, $logger);
    $repository = new \Bveing\MBuddy\Motif\MasterRepository($sysExClient);

    $master = yield $repository->get(\Bveing\MBuddy\Motif\MasterId::fromInt(0));

    $logger->info("Master: [{$master->getName()}]");
});
