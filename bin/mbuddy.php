#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use \bviguier\RtMidi;
use \bviguier\MBuddy;

$logHandlers = [
    (new \Monolog\Handler\StreamHandler(STDOUT))
        ->setFormatter(new \Monolog\Formatter\LineFormatter(
            "[%datetime%][%channel%](%level_name%): %message% %context% %extra%\n",
            'H:m:i',
            true
        )),
];
$cmdLogger = new \Monolog\Logger('MBuddy', $logHandlers);
$midiBrowser = new RtMidi\MidiBrowser();
$config = new MBuddy\Config(__DIR__.'/../config.'.strtolower(PHP_OS_FAMILY).'.php');

try {
    /** @var array<MBuddy\Device> $devices */
    $devices = [
        $impulse = new MBuddy\Device\Impulse(
            $midiBrowser->openInput($config->get(MBuddy\Config::IMPULSE_IN)),
            $midiBrowser->openOutput($config->get(MBuddy\Config::IMPULSE_OUT)),
            new MBuddy\MidiSyxBank(__DIR__ . '/../var'),
            new \Monolog\Logger('Impulse', $logHandlers),
        ),
        $pa50 = new MBuddy\Device\Pa50(
            $midiBrowser->openInput($config->get(MBuddy\Config::PA50_IN)),
            $midiBrowser->openOutput($config->get(MBuddy\Config::PA50_OUT)),
            new \Monolog\Logger('Pa50', $logHandlers),
        ),
    ];
} catch (RtMidi\Exception\MidiException $exception) {
    $error = "{$exception->getMessage()}\n";
    $error.= "Available Inputs:\n";
    foreach($midiBrowser->availableInputs() as $inputName) {
        $error.= " * [IN] '$inputName'\n";
    }
    $error.= "Available Outputs:\n";
    foreach($midiBrowser->availableOutputs() as $outputName) {
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
