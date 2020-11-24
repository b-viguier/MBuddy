<?php

require __DIR__.'/../vendor/autoload.php';

use \bviguier\RtMidi;
use \bviguier\MBuddy;

$config = new MBuddy\Config(__DIR__.'/../config.php');

$impulseBank = new MBuddy\MidiSyxBank($config->get(MBuddy\Config::IMPULSE_BANK_FOLDER));

$midiBrowser = new RtMidi\MidiBrowser();
$impulseInput = $midiBrowser->openInput($config->get(MBuddy\Config::IMPULSE_IN));
$impulseOutput = $midiBrowser->openOutput($config->get(MBuddy\Config::IMPULSE_OUT));
$pa50Input = $midiBrowser->openInput($config->get(MBuddy\Config::PA50_IN));
$pa50Output = $midiBrowser->openOutput($config->get(MBuddy\Config::PA50_OUT));

$impulseInput->allow(RtMidi\Input::ALLOW_SYSEX);

$impulseRouter = MBuddy\Router::fromHandlers(
    new MBuddy\Impulse\Handler\Sysex($impulseBank, $pa50Output),
    new MBuddy\Impulse\Handler\Forward($pa50Output),
);

$pa50Router = MBuddy\Router::fromHandlers(
    $prgChangeHandler = new MBuddy\Pa50\Handler\ProgramChange($impulseBank, $impulseOutput),
    new MBuddy\Pa50\Handler\BankChange($prgChangeHandler),
);

echo "Running…\n";
while (true) {
    do {
        if ($msgImpulse = $impulseInput->pullMessage()) {
            $impulseRouter->handle($msgImpulse);
        }
        if($msgPa50 = $pa50Input->pullMessage()) {
            $pa50Router->handle($msgPa50);
        }
    } while($msgImpulse || $msgPa50);
    usleep(1000);
}
