<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class Master
{
    public const MODE_VOICE = 0;
    public const MODE_PERFORMANCE = 1;
    public const MODE_PATTERN = 2;
    public const MODE_SONG = 3;


    public const FUNC_TONE1 = 0;
    public const FUNC_TONE2 = 1;
    public const FUNC_ARP = 2;
    public const FUNC_REV = 3;
    public const FUNC_CHO = 4;
    public const FUNC_PAN = 5;
    public const FUNC_ZONE = 6;

    private array $zones = [];
    private Program $program;

    public function __construct(
        private int $id,
        private string $name = 'New',
        private int $mode = self::MODE_SONG,
        ?Program $program = null,
        private bool $zoneEnabled = true,
        private int $knobSliderFunction = self::FUNC_ZONE,
    ) {
        $this->program = $program ?? new Program(0, 0, 0);
        $this->zones = [
            new MasterZone(id: 0),
            new MasterZone(id: 1),
            new MasterZone(id: 2),
            new MasterZone(id: 3),
            new MasterZone(id: 4),
            new MasterZone(id: 5),
            new MasterZone(id: 6),
            new MasterZone(id: 7),
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getProgram(): Program
    {
        return $this->program;
    }

    public function isZoneEnabled(): bool
    {
        return $this->zoneEnabled;
    }

    public function knobSliderFunction(): int
    {
        return $this->knobSliderFunction;
    }

    public function getZone(int $index): MasterZone
    {
        return $this->zones[$index];
    }

    public function getSysEx(): \iterable
    {
        // Header block
        yield SysEx\BulkDumpBlock::create(
            byteCount: 0x00,
            address: new SysEx\Address(0x0E, 0x70, $this->id),
            data: [],
        );

        // Common block
        yield SysEx\BulkDumpBlock::create(
            byteCount: 0x1F,
            address: new SysEx\Address(0x33, 0x00, 0x00),
            data: [
                ...unpack('A20', str_pad($this->name, 20)),
                ...[0x00, 0x00, 0x00, 0x00, 0x00], // Reserved
                $this->mode,
                $this->program->getBankMsb(),
                $this->program->getBankLsb(),
                $this->program->getNumber(),
                $this->zoneEnabled,
                $this->knobSliderFunction,
            ],
        );

        foreach ($this->zones as $zone) {
            yield $zone->getSysEx();
        }

        // Footer block
        yield SysEx\BulkDumpBlock::create(
            byteCount: 0x00,
            address: new SysEx\Address(0x0F, 0x70, $this->id),
            data: [],
        );
    }
}
