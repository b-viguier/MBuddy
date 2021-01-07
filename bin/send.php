#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use bviguier\RtMidi;
use bviguier\MBuddy;

$config = MBuddy\Config::fromOsConfigFile();
$log = $config->logger('MBuddy:copy');

$id = (int) ($argv[1] ?? 0);

$log->notice("Loading Preset in Impulse…");
$config->deviceImpulse()->doLoadPreset()(new MBuddy\Preset(
        MBuddy\Device\Impulse::BANK_MSB,
        MBuddy\Device\Impulse::BANK_LSB,
        $id,
));
$log->notice("Done");

