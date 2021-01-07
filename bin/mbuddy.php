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

$impulse->onPresetSaved($pa50->doSaveExternalPreset());
$impulse->onMidiEvent($pa50->doPlayEvent());
$pa50->onExternalPresetLoaded($impulse->doLoadPreset());

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
