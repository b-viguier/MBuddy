#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use bviguier\RtMidi;
use bviguier\MBuddy;

$midiBrowser = new RtMidi\MidiBrowser();

$config = new MBuddy\Config(__DIR__.'/../config.'.strtolower(PHP_OS_FAMILY).'.php');
$bank = new MBuddy\MidiSyxBank($config->get(MBuddy\Config::IMPULSE_BANK_FOLDER));

$id = (int) ($argv[1] ?? 0);
echo "Loading [$id]…\n";
if(null === $data = $bank->load($id)) {
    die("Cannot Load [$id]\n");
}

echo "Sending...\n";
$output = $midiBrowser->openOutput($config->get(MBuddy\Config::IMPULSE_OUT));
$output->send(bviguier\RtMidi\Message::fromBinString($data));
echo "Done\n";

