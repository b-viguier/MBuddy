#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use bviguier\RtMidi;
use bviguier\MBuddy;

$config = new MBuddy\Config(__DIR__.'/../config.'.strtolower(PHP_OS_FAMILY).'.php');
$bank = new MBuddy\MidiSyxBank(__DIR__ . '/../var');

$id = (int) ($argv[1] ?? 0);
$unsafeName = $argv[2] ?? 'New';

echo "Loading [$id]…\n";
if(null === $data = $bank->load($id)) {
    die("Cannot Load [$id]\n");
}

$patch = MBuddy\Device\Impulse\Patch::fromBinString($data)->withName($unsafeName);
$safeName = $patch->name();
echo "Writing [$safeName]…\n";
if(null !== $bank->findByName($safeName)) {
    die("Name [$safeName] already in use\n");
}
$newId = $bank->save($patch->name(), $patch->toBinString());

echo "Done: [$id] copied to [$newId][{$patch->name()}]\n";
