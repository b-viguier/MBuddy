#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use bviguier\RtMidi;
use bviguier\MBuddy;

$config = MBuddy\Config::fromOsConfigFile();
$bank = $config->midiSyxBank();
$log = $config->logger('MBuddy:copy');

$id = (int) ($argv[1] ?? 0);
$unsafeName = $argv[2] ?? 'New';

$log->notice("Loading [$id]…");
if(null === $data = $bank->load($id)) {
    $log->emergency("Cannot Load [$id]");
    exit(1);
}

$patch = MBuddy\Device\Impulse\Patch::fromBinString($data)->withName($unsafeName);
$safeName = $patch->name();
$log->notice("Writing [$safeName]…");
if(null !== $bank->findByName($safeName)) {
    $log->emergency("Name [$safeName] already in use");
    exit(1);
}
$newId = $bank->save($patch->name(), $patch->toBinString());

$log->notice("Done: [$id] copied to [$newId][{$patch->name()}]");
