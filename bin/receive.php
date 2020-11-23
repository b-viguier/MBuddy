<?php

require __DIR__.'/../vendor/autoload.php';

use \bviguier\RtMidi;

$browser = new RtMidi\MidiBrowser();
$input = $browser->openInput('Impulse  Impulse ');
$input->allow(RtMidi\Input::ALLOW_SYSEX);

echo "Waiting...\n";
while (true) {
    if (null === $msg = $input->pullMessage()) {
        usleep(10_000);
        continue;
    }

    if ($msg->byte(0) !== 0xF0) {
        continue;
    }

    echo "Receiving Sysex…\n";
    $title = trim(substr($msg->toBinString(), 7, 8));
    echo "Title [$title]\n";
    file_put_contents(__DIR__."/../var/$title.syx", $msg->toBinString());
}

