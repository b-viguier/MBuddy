#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use \bviguier\RtMidi;
use \bviguier\MBuddy;

$config = MBuddy\Config::fromOsConfigFile();
$cmdLogger = $config->logger('MBuddy');

try {
    /** @var array<MBuddy\Device> $devices */
    $devices = [
        $impulse = $config->deviceImpulse(),
        $pa50 = $config->devicePa50(),
    ];
    $cmdLogger->info("Looking for iPad device…");
    if($ipad = $config->deviceIPad()) {
        $cmdLogger->info("iPad found");
    } else {
        $cmdLogger->info("No iPad");
    }
} catch (RtMidi\Exception\MidiException $exception) {
    $error = "{$exception->getMessage()}\n";
    $error.= "Available Inputs:\n";
    foreach($config->midiBrowser()->availableInputs() as $inputName) {
        $error.= " * [IN] '$inputName'\n";
    }
    $error.= "Available Outputs:\n";
    foreach($config->midiBrowser()->availableOutputs() as $outputName) {
        $error.= " * [OUT] '$outputName'\n";
    }
    $cmdLogger->critical($error);
    exit(1);
}

$impulse->onSongIdModified($pa50->doModifySongIdOfCurrentPerformance());
$impulse->onMidiEvent($pa50->doPlayEvent());
$pa50->onSongChanged($ipad ? function (MBuddy\SongId $songId) use ($impulse, $ipad): void {
    $impulse->doLoadSong()($songId);
    $ipad->doLoadSong()($songId);
} : $impulse->doLoadSong());

const MSG_LIMIT = 2;
$cmdLogger->notice("Running…");
while (true) {
    do {
        $activity = false;
        foreach ($devices as $device) {
            $activity |= MSG_LIMIT === $device->process(MSG_LIMIT);
        }
    } while ($activity);
    usleep(1000);
}
