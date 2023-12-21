<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

Amp\Loop::run(function() {
    $browser = new \bviguier\RtMidi\MidiBrowser("/opt/homebrew/lib/librtmidi.dylib");
    $input = $browser->openInput("WIDI Jack Bluetooth");
    $output = $browser->openOutput("WIDI Jack Bluetooth");

    $driver = new \Bveing\MBuddy\Infrastructure\Motif\MidiDriver\RtMidi($input, $output);
    $repository = new \Bveing\MBuddy\Motif\MasterRepository($driver);

    $repository->get(\Bveing\MBuddy\Motif\MasterId::editBuffer(), function(\Bveing\MBuddy\Motif\Master $master) {
        display($master);
    });

    $repository->get(\Bveing\MBuddy\Motif\MasterId::fromInt(3), function(\Bveing\MBuddy\Motif\Master $master) {
        display($master);
    });

    $repository->get(\Bveing\MBuddy\Motif\MasterId::fromInt(5), function(\Bveing\MBuddy\Motif\Master $master) {
        display($master);
    });
});

function display(\Bveing\MBuddy\Motif\Master $master) {
    echo "Master: [{$master->getName()}]\n";
}
