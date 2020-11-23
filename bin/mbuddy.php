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

const CHANNEL_16 = 0x0F;
const PROGRAM_CHANGE = 0xC0;
const CONTROL_CHANGE = 0xB0;
const CC_BANK_SELECT_MSB = 0x00;
const CC_BANK_SELECT_LSB = 0x20;

echo "Running…\n";
while (true) {
    if ($msg = $impulseInput->pullMessage()) {
        $firstByte = $msg->byte(0);
        if ($firstByte === 0xF0) {
            $name = trim(substr($msg->toBinString(), 7, 8));
            $prgId = $impulseBank->save($name, $msg);
            echo "Saved [$name][$prgId]\n";

            $pa50Output->send(RtMidi\Message::fromIntegers(CONTROL_CHANGE + CHANNEL_16, CC_BANK_SELECT_MSB, 0));
            $pa50Output->send(RtMidi\Message::fromIntegers(CONTROL_CHANGE + CHANNEL_16, CC_BANK_SELECT_LSB, 0));
            $pa50Output->send(RtMidi\Message::fromIntegers(PROGRAM_CHANGE + CHANNEL_16, $prgId));
            continue;
        }

        $pa50Output->send($msg);
    } elseif($msg = $pa50Input->pullMessage()) {
        switch($msg->byte(0)) {
            case PROGRAM_CHANGE + CHANNEL_16:
                $prgId = $msg->byte(1);
                if($msg = $impulseBank->load($prgId))
                {
                    echo "Impulse Program Change [$prgId]\n";
                    $impulseOutput->send($msg);
                }
                break;
            case CONTROL_CHANGE + CHANNEL_16:
                switch ($msg->byte(1)) {
                    case CC_BANK_SELECT_MSB:
                        $impulseBank->setBankMSB($msg->byte(2));
                        break;
                    case CC_BANK_SELECT_LSB:
                        $impulseBank->setBankLSB($msg->byte(2));
                        break;
                    default:
                        continue 3;
                }
                break;
            default:
                continue 2;
        }
        continue;
    } else {
        usleep(1000);
    }
}
