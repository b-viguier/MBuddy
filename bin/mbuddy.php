<?php

require __DIR__.'/../vendor/autoload.php';

use \bviguier\RtMidi;
use \bviguier\MBuddy;

$midiBrowser = new RtMidi\MidiBrowser();

$config = new MBuddy\Config(__DIR__.'/../config.php');

/** @var array<MBuddy\Device> $devices */
$devices = [
    $impulse = new MBuddy\Device\Impulse(
        $midiBrowser->openInput($config->get(MBuddy\Config::IMPULSE_IN)),
        $midiBrowser->openOutput($config->get(MBuddy\Config::IMPULSE_OUT)),
        new MBuddy\MidiSyxBank($config->get(MBuddy\Config::IMPULSE_BANK_FOLDER))
    ),
    $pa50 = new MBuddy\Device\Pa50(
        $midiBrowser->openInput($config->get(MBuddy\Config::PA50_IN)),
        $midiBrowser->openOutput($config->get(MBuddy\Config::PA50_OUT)),
    ),
];

$impulse->onPresetSaved($pa50->doSaveExternalPreset());
$impulse->onMidiEvent($pa50->doPlayEvent());
$pa50->onExternalPresetLoaded($impulse->doLoadPreset());

const MSG_LIMIT = 2;
echo "Running…\n";
while (true) {
    do {
        $activity = false;
        foreach ($devices as $device) {
            $activity |= MSG_LIMIT === $device->process(MSG_LIMIT);
        }
    } while ($activity);
    usleep(1000);
}
