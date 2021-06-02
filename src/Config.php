<?php

namespace bviguier\MBuddy;

use bviguier\RtMidi;
use Monolog;
use Psr\Log;

class Config
{
    public const IMPULSE_IN = 'impulse_in';
    public const IMPULSE_OUT = 'impulse_out';
    public const PA50_IN = 'pa50_in';
    public const PA50_OUT = 'pa50_out';
    public const IPAD_IN = 'ipad_in';
    public const IPAD_OUT = 'ipad_out';

    /**
     * @param array<string,string> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    static public function fromOsConfigFile(): self
    {
        return new self(require __DIR__.'/../config/'.strtolower(PHP_OS_FAMILY).'.php');
    }

    public function midiBrowser(): RtMidi\MidiBrowser
    {
        return $this->midiBrowser ?? $this->midiBrowser = new RtMidi\MidiBrowser();
    }

    public function logger(string $channel): Log\LoggerInterface
    {
        return $this->loggers[$channel] ??
            $this->loggers[$channel] = new \Monolog\Logger(
                $channel, [$this->logHandler()]
            );
    }

    public function midiSyxBank(): MidiSyxBank
    {
        return $this->midiSyxBank ?? $this->midiSyxBank = new MidiSyxBank(__DIR__.'/../var');
    }

    public function deviceImpulse(): Device\Impulse
    {
        return $this->deviceImpulse ?? $this->deviceImpulse = new Device\Impulse(
                $this->midiBrowser()->openInput($this->get(self::IMPULSE_IN)),
                $this->midiBrowser()->openOutput($this->get(self::IMPULSE_OUT)),
                $this->midiSyxBank(),
                $this->logger('Impulse'),
            );
    }

    public function devicePa50(): Device\Pa50
    {
        return $this->devicePa50 ?? $this->devicePa50 = new Device\Pa50(
                $this->midiBrowser()->openInput($this->get(self::PA50_IN)),
                $this->midiBrowser()->openOutput($this->get(self::PA50_OUT)),
                $this->logger('Pa50'),
            );
    }

    public function deviceIPad(): Device\IPad
    {
        return $this->deviceIPad ?? $this->deviceIPad = new Device\IPad(
                $this->midiBrowser()->openInput($this->get(self::IPAD_IN)),
                $this->midiBrowser()->openOutput($this->get(self::IPAD_OUT)),
                $this->logger('IPad'),
            );
    }

    /** @var array<string,string> */
    private array $config = [];
    private RtMidi\MidiBrowser $midiBrowser;
    private Monolog\Handler\HandlerInterface $logHandler;
    /** @var array<Log\LoggerInterface> */
    private array $loggers = [];
    private MidiSyxBank $midiSyxBank;
    private Device\Impulse $deviceImpulse;
    private Device\Pa50 $devicePa50;
    private Device\IPad $deviceIPad;

    private function logHandler(): Monolog\Handler\HandlerInterface
    {
        return $this->logHandler ?? $this->logHandler = (new \Monolog\Handler\StreamHandler(STDOUT))
                ->setFormatter(new Monolog\Formatter\LineFormatter(
                    "[%datetime%][%channel%](%level_name%): %message% %context% %extra%\n",
                    'H:m:i',
                    true
                ));
    }

    private function get(string $paramName): string
    {
        if (!isset($this->config[$paramName])) {
            throw new \InvalidArgumentException("Missing '$paramName' config parameter.");
        }
        if (!is_string($this->config[$paramName])) {
            throw new \TypeError("Config parameter '$paramName' must be a string");
        }

        return $this->config[$paramName];
    }
}