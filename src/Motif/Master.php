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

    public const DUMP_NB_BLOCKS = 11;

    private function __construct(
        private MasterId $id,
        private string $name,
        private int $mode,
        private Program $program,
        private bool $zoneEnabled,
        private int $knobSliderFunction,
        private MasterZone $zone0,
        private MasterZone $zone1,
        private MasterZone $zone2,
        private MasterZone $zone3,
        private MasterZone $zone4,
        private MasterZone $zone5,
        private MasterZone $zone6,
        private MasterZone $zone7,
    ) {
    }

    public static function default(): self
    {
        return new self(
            id: MasterId::editBuffer(),
            name: 'New',
            mode: self::MODE_SONG,
            program: new Program(0, 0, 0),
            zoneEnabled: true,
            knobSliderFunction: self::FUNC_ZONE,
            zone0: MasterZone::default(0),
            zone1: MasterZone::default(1),
            zone2: MasterZone::default(2),
            zone3: MasterZone::default(3),
            zone4: MasterZone::default(4),
            zone5: MasterZone::default(5),
            zone6: MasterZone::default(6),
            zone7: MasterZone::default(7),
        );
    }

    public static function fromBulkDumpBlocks(
        SysEx\BulkDumpBlock $headerBlock,
        SysEx\BulkDumpBlock $commonBlock,
        SysEx\BulkDumpBlock $zone0Block,
        SysEx\BulkDumpBlock $zone1Block,
        SysEx\BulkDumpBlock $zone2Block,
        SysEx\BulkDumpBlock $zone3Block,
        SysEx\BulkDumpBlock $zone4Block,
        SysEx\BulkDumpBlock $zone5Block,
        SysEx\BulkDumpBlock $zone6Block,
        SysEx\BulkDumpBlock $zone7Block,
        SysEx\BulkDumpBlock $footerBlock,
    ): self {
        assert($headerBlock->isHeaderBlock());
        assert($footerBlock->isFooterBlock());

        $headerAddress = $headerBlock->getAddress();
        assert($footerBlock->getAddress()->m() === $headerAddress->m());
        assert($footerBlock->getAddress()->l() === $headerAddress->l());

        $masterId = match ($headerAddress->m()) {
            0x70 => MasterId::fromInt($headerAddress->l()),
            0x7F => MasterId::editBuffer(),
            default => throw new \RuntimeException('Invalid MasterId'),
        };

        assert($commonBlock->getAddress()->toArray() === [0x33, 0x00, 0x00]);
        $commonData = $commonBlock->getData();

        return new self(
            id: $masterId,
            name: trim(pack('C20', ...array_slice($commonData, 0, 20))),
            mode: $commonData[0x19],
            program: new Program($commonData[0x1A], $commonData[0x1B], $commonData[0x1C]),
            zoneEnabled: (bool)$commonData[0x1D],
            knobSliderFunction: $commonData[0x1E],
            zone0: MasterZone::fromBulkDump($zone0Block),
            zone1: MasterZone::fromBulkDump($zone1Block),
            zone2: MasterZone::fromBulkDump($zone2Block),
            zone3: MasterZone::fromBulkDump($zone3Block),
            zone4: MasterZone::fromBulkDump($zone4Block),
            zone5: MasterZone::fromBulkDump($zone5Block),
            zone6: MasterZone::fromBulkDump($zone6Block),
            zone7: MasterZone::fromBulkDump($zone7Block),
        );
    }

    public function withParameterChange(SysEx\ParameterChange $parameterChange): self
    {
        // TODO
        return $this;
    }

    public function getId(): MasterId
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

    public function getZone0(): MasterZone
    {
        return $this->zone0;
    }

    public function getZone1(): MasterZone
    {
        return $this->zone1;
    }

    public function getZone2(): MasterZone
    {
        return $this->zone2;
    }

    public function getZone3(): MasterZone
    {
        return $this->zone3;
    }

    public function getZone4(): MasterZone
    {
        return $this->zone4;
    }

    public function getZone5(): MasterZone
    {
        return $this->zone5;
    }

    public function getZone6(): MasterZone
    {
        return $this->zone6;
    }

    public function getZone7(): MasterZone
    {
        return $this->zone7;
    }

    /**
     * @return iterable<SysEx\BulkDumpBlock>
     */
    public function getBulkDumpBlocks(): iterable
    {
        $masterAddress = $this->id->isEditBuffer()
            ? [0x7F, 0x0]
            : [0x70, $this->id->toInt()];

        yield SysEx\BulkDumpBlock::createHeaderBlock(...$masterAddress);

        $name = unpack('A20', str_pad($this->name, 20));
        assert(is_array($name));

        // Common block
        yield SysEx\BulkDumpBlock::create(
            byteCount: 0x1F,
            address: new SysEx\Address(0x33, 0x00, 0x00),
            data: [
                ...$name,
                ...[0x00, 0x00, 0x00, 0x00, 0x00], // Reserved
                0x19 => $this->mode,
                0x1A => $this->program->getBankMsb(),
                0x1B => $this->program->getBankLsb(),
                0x1C => $this->program->getNumber(),
                0x1D => $this->zoneEnabled,
                0x1E => $this->knobSliderFunction,
            ],
        );

        // Zones
        yield $this->zone0->getSysEx();
        yield $this->zone1->getSysEx();
        yield $this->zone2->getSysEx();
        yield $this->zone3->getSysEx();
        yield $this->zone4->getSysEx();
        yield $this->zone5->getSysEx();
        yield $this->zone6->getSysEx();
        yield $this->zone7->getSysEx();

        yield SysEx\BulkDumpBlock::createFooterBlock(...$masterAddress);
    }

    public static function getDumpRequest(MasterId $id): SysEx\DumpRequest
    {
        $address = $id->isEditBuffer()
            ? new SysEx\Address(0x0E, 0x7F, 0x00)
            : new SysEx\Address(0x0E, 0x70, $id->toInt());

        return new SysEx\DumpRequest($address);
    }
}

assert(count((new \ReflectionMethod(Master::class, 'fromBulkDumpBlocks'))->getParameters()) === Master::DUMP_NB_BLOCKS);
