<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Motif;

class Master
{
    public const DUMP_NB_BLOCKS = 11;

    private function __construct(
        private Master\Id $id,
        private string $name,
        private Master\Mode $mode,
        private Program $program,
        private bool $zoneEnabled,
        private Master\KnobSliderFunction $knobSliderFunction,
        private Master\Zone $zone0,
        private Master\Zone $zone1,
        private Master\Zone $zone2,
        private Master\Zone $zone3,
        private Master\Zone $zone4,
        private Master\Zone $zone5,
        private Master\Zone $zone6,
        private Master\Zone $zone7,
    ) {
    }

    public static function default(): self
    {
        return new self(
            id: Master\Id::editBuffer(),
            name: 'New',
            mode: Master\Mode::SONG(),
            program: new Program(0, 0, 0),
            zoneEnabled: true,
            knobSliderFunction: Master\KnobSliderFunction::ZONE(),
            zone0: Master\Zone::default(0),
            zone1: Master\Zone::default(1),
            zone2: Master\Zone::default(2),
            zone3: Master\Zone::default(3),
            zone4: Master\Zone::default(4),
            zone5: Master\Zone::default(5),
            zone6: Master\Zone::default(6),
            zone7: Master\Zone::default(7),
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
            0x70 => Master\Id::fromInt($headerAddress->l()),
            0x7F => Master\Id::editBuffer(),
            default => throw new \RuntimeException('Invalid MasterId'),
        };

        assert($commonBlock->getAddress()->toArray() === [0x33, 0x00, 0x00]);
        $commonData = $commonBlock->getData();

        return new self(
            id: $masterId,
            name: trim(pack('C20', ...array_slice($commonData, 0, 20))),
            mode: Master\Mode::from($commonData[0x19]),
            program: new Program($commonData[0x1A], $commonData[0x1B], $commonData[0x1C]),
            zoneEnabled: (bool)$commonData[0x1D],
            knobSliderFunction: Master\KnobSliderFunction::from($commonData[0x1E]),
            zone0: Master\Zone::fromBulkDump($zone0Block),
            zone1: Master\Zone::fromBulkDump($zone1Block),
            zone2: Master\Zone::fromBulkDump($zone2Block),
            zone3: Master\Zone::fromBulkDump($zone3Block),
            zone4: Master\Zone::fromBulkDump($zone4Block),
            zone5: Master\Zone::fromBulkDump($zone5Block),
            zone6: Master\Zone::fromBulkDump($zone6Block),
            zone7: Master\Zone::fromBulkDump($zone7Block),
        );
    }

    public function withParameterChange(SysEx\ParameterChange $parameterChange): self
    {
        // TODO
        return $this;
    }

    public function getId(): Master\Id
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMode(): Master\Mode
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

    public function knobSliderFunction(): Master\KnobSliderFunction
    {
        return $this->knobSliderFunction;
    }

    public function getZone0(): Master\Zone
    {
        return $this->zone0;
    }

    public function getZone1(): Master\Zone
    {
        return $this->zone1;
    }

    public function getZone2(): Master\Zone
    {
        return $this->zone2;
    }

    public function getZone3(): Master\Zone
    {
        return $this->zone3;
    }

    public function getZone4(): Master\Zone
    {
        return $this->zone4;
    }

    public function getZone5(): Master\Zone
    {
        return $this->zone5;
    }

    public function getZone6(): Master\Zone
    {
        return $this->zone6;
    }

    public function getZone7(): Master\Zone
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
                0x19 => $this->mode->getValue(),
                0x1A => $this->program->getBankMsb(),
                0x1B => $this->program->getBankLsb(),
                0x1C => $this->program->getNumber(),
                0x1D => $this->zoneEnabled,
                0x1E => $this->knobSliderFunction->getValue(),
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

    public static function getDumpRequest(Master\Id $id): SysEx\DumpRequest
    {
        $address = $id->isEditBuffer()
            ? new SysEx\Address(0x0E, 0x7F, 0x00)
            : new SysEx\Address(0x0E, 0x70, $id->toInt());

        return new SysEx\DumpRequest($address);
    }
}

assert(count((new \ReflectionMethod(Master::class, 'fromBulkDumpBlocks'))->getParameters()) === Master::DUMP_NB_BLOCKS);
