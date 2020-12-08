<?php

namespace bviguier\MBuddy;

class Config
{
    public const IMPULSE_IN = 'impulse_in';
    public const IMPULSE_OUT = 'impulse_out';
    public const PA50_IN = 'pa50_in';
    public const PA50_OUT = 'pa50_out';

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->data = require $filepath;
    }

    public function get(string $key): string
    {
        assert(isset($this->data[$key]));

        return $this->data[$key];
    }


    private string $filepath;
    /** @var array<string,string> */
    private array $data;
}