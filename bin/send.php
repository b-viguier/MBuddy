<?php

require __DIR__.'/../vendor/autoload.php';

use \bviguier\RtMidi;

$file = $argv[1];
$browser = new RtMidi\MidiBrowser();
$output = $browser->openOutput('Impulse  Impulse ');

echo "Sending...\n";
$data = file_get_contents(__DIR__."/../var/$file.syx");
$msg = RtMidi\Message::fromBinString($data);
$output->send($msg);
echo "Done\n";

