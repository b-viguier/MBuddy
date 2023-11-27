<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class Part
{
    public function __construct(
        private Channel $channel,
        private string $voiceName,
        private bool $enabled,
    ) {
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function getVoiceName(): string
    {
        return $this->voiceName;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
