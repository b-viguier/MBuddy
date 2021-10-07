<?php

namespace bviguier\MBuddy;

use bviguier\MBuddy\WebSocket;
use bviguier\RtMidi;
use Monolog;
use Psr\Log;

class Config
{
    public const IMPULSE_IN = 'impulse_in';
    public const IMPULSE_OUT = 'impulse_out';
    public const PA50_IN = 'pa50_in';
    public const PA50_OUT = 'pa50_out';
    public const MBUDDY_HOST = 'host';

    /**
     * @param array<string,string> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->playlist = Playlist::fromFile(__DIR__ . '/../var/playlist.php');
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
                $this->midiBrowser()->openInput($this->getInputName(self::IMPULSE_IN)),
                $this->midiBrowser()->openOutput($this->getOutputName(self::IMPULSE_OUT)),
                $this->midiSyxBank(),
                $this->logger('Impulse'),
            );
    }

    public function devicePa50(): Device\Pa50
    {
        return $this->devicePa50 ?? $this->devicePa50 = new Device\Pa50(
                $this->midiBrowser()->openInput($this->getInputName(self::PA50_IN)),
                $this->midiBrowser()->openOutput($this->getOutputName(self::PA50_OUT)),
                $this->logger('Pa50'),
            );
    }

    public function deviceIPad(): Device\IPad
    {
        return $this->deviceIPad ?? $this->deviceIPad = new Device\IPad(
            new WebSocket\Output($this->server()),
            $this->logger('IPad'),
        );
    }

    public function playlist(): Playlist
    {
        return $this->playlist;
    }

    public function server(): WebSocket\Server
    {
        return $this->server ?? $this->server = new WebSocket\Server(
            $this->get(self::MBUDDY_HOST),
            12380
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
    private Playlist $playlist;
    private WebSocket\Server $server;

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

    private function getInputName(string $paramName): string
    {
        return $this->searchClosestName($this->get($paramName), $this->midiBrowser()->availableInputs());
    }

    private function getOutputName(string $paramName): string
    {
        return $this->searchClosestName($this->get($paramName), $this->midiBrowser()->availableOutputs());
    }

    /**
     * @param array<string>  $haystack
     */
    private function searchClosestName(string $needle, array $haystack): string
    {
        foreach ($haystack as $entry) {
            if (strpos($entry, $needle) !== false) {
                return $entry;
            }
        }

        return $needle;
    }
}