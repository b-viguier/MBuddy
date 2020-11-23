<?php

namespace bviguier\MBuddy;

class Config
{
    public const IMPULSE_IN = 'impulse_in';
    public const IMPULSE_OUT = 'impulse_out';
    public const PA50_IN = 'pa50_in';
    public const PA50_OUT = 'pa50_out';
    public const IMPULSE_BANK_FOLDER = 'impulse_bank_folder';

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->data = require $filepath;
    }

    /**
     * @return $this
     */
    public function set(string $key, string $value): self
    {
        $this->data[$key] = $value;
    }

    /**
     * @return string|null
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }


    private string $filepath;
    private array $data;
}