import Motif


class Block(object):
    def __init__(self, msg, address, size):
        if msg[0:5] != [0xF0, 0x43, 0x00, 0x7F, 0x03] or msg[-1] != 0xF7:
            raise ValueError("Not a bulk message")

        if address != tuple(msg[7:10]):
            raise ValueError("Invalid address", address, tuple(msg[7:10]))

        if msg[5] * 128 + msg[6] != size:
            raise ValueError("Unexpected data size")

        if size + 12 != len(msg):
            raise ValueError("Invalid byte count")

        if sum(msg[5:-1]) % 128 != 0:
            raise ValueError("Invalid checksum")

        self.msg = msg

    def address(self):
        return tuple(self.msg[7:10])

    def get_data(self, offset, size=1):
        data = self.msg[10 + offset:10 + offset + size]
        return data[0] if size == 1 else tuple(data)

    def get_msg(self):
        checksum = 128 - sum(self.msg[5:-2]) % 128
        self.msg[-2] = checksum
        return self.msg


class MasterHeaderBlock(Block):
    def __init__(self, msg, master_id):
        if master_id < 0 or master_id > 127:
            super().__init__(msg, (0x0E, 0x7F, 0), 0)
        else:
            super().__init__(msg, (0x0E, 0x70, master_id), 0)

    def get_master_id(self):
        return self.address()[2]

    def print(self):
        print({
            'master_id': self.get_master_id(),
        })


class MasterFooterBlock(Block):
    def __init__(self, msg, master_id):
        if master_id < 0 or master_id > 127:
            super().__init__(msg, (0x0F, 0x7F, 0), 0)
        else:
            super().__init__(msg, (0x0F, 0x70, master_id), 0)

    def print(self):
        print('end')


class MasterCommonBlock(Block):
    def __init__(self, msg):
        super().__init__(msg, (0x33, 0x00, 0x00), 0x1F)

    def get_title(self):
        return ''.join(map(chr, self.get_data(0x00, 20)))

    def get_mode(self):
        return Motif.MasterMode(self.get_data(0x19))

    def get_bank_program(self):
        return self.get_data(0x1A, 3)

    def get_zones_enabled(self):
        return True if self.get_data(0x1D) else False

    def print(self):
        print({
            'title': self.get_title(),
            'mode': self.get_mode(),
            'bank_program': self.get_bank_program(),
            'zones_enabled' : self.get_zones_enabled(),
        })


class MasterZone(Block):
    def __init__(self, msg, zone_id):
        super().__init__(msg, (0x32, zone_id, 0x00), 0x10)

    def get_zone_id(self):
        return self.address()[1]

    def get_transmit_channel(self):
        return 0b00001111 & self.get_data(0)

    def is_transmit_enabled(self):
        return True if 0b00010000 & self.get_data(0x0) else False

    def is_internal_enabled(self):
        return True if 0b00100000 & self.get_data(0x0) else False

    def get_transpose_octave(self):
        return self.get_data(0x1) - 0x40

    def get_transpose_semitone(self):
        return self.get_data(0x2) - 0x40

    def get_note_low(self):
        return self.get_data(0x3)

    def get_note_high(self):
        return self.get_data(0x4)

    def get_volume(self):
        return self.get_data(0x6)

    def get_pan(self):
        return self.get_data(0x7)

    def get_bank_program(self):
        return self.get_data(0x8, 3)

    def is_internal_bank_select_enabled(self):
        return True if self.get_data(0xB) & 0b00000001 else False

    def is_internal_program_change_enabled(self):
        return True if self.get_data(0xB) & 0b00000010 else False

    def is_transmit_bank_select_enabled(self):
        return True if self.get_data(0xB) & 0b00001000 else False

    def is_transmit_program_change_enabled(self):
        return True if self.get_data(0xB) & 0b00010000 else False

    def print(self):
        print({
            'zone_id': self.get_zone_id(),
            'transmit_channel': self.get_transmit_channel(),
            'transmit_enabled': self.is_transmit_enabled(),
            'internal_enabled': self.is_internal_enabled(),
            'transpose_octave': self.get_transpose_octave(),
            'transpose_semitone': self.get_transpose_semitone(),
            'note_low': self.get_note_low(),
            'note_high': self.get_note_high(),
            'volume': self.get_volume(),
            'pan': self.get_pan(),
            'bank_program': self.get_bank_program(),
            'internal_bank_select_enabled': self.is_internal_bank_select_enabled(),
            'internal_program_change_enabled': self.is_internal_program_change_enabled(),
            'transmit_bank_select_enabled': self.is_transmit_bank_select_enabled(),
            'transmit_program_change_enabled': self.is_transmit_program_change_enabled(),
        })

class Dump(object):
    def __init__(self, blocks):
        if len(blocks) != self.get_nb_blocks():
            raise ValueError("Invalid number of blocks in dump")
        self.blocks = blocks

    @staticmethod
    def get_nb_blocks():
        return 0

    @staticmethod
    def get_dump_request(address):
        return 0xF0, 0x43, 0x20, 0x7F, 0x03, *address, 0xF7


class MasterDump(object):
    def __init__(self, master_id, msgs):
        if len(msgs) != self.get_nb_blocks():
            raise ValueError("Invalid number of blocks in MasterDump")
        self.blocks = (
                          MasterHeaderBlock(msgs[0], master_id),
                          MasterCommonBlock(msgs[1]),
                          MasterZone(msgs[2], 0),
                          MasterZone(msgs[3], 1),
                          MasterZone(msgs[4], 2),
                          MasterZone(msgs[5], 3),
                          MasterZone(msgs[6], 4),
                          MasterZone(msgs[7], 5),
                          MasterZone(msgs[8], 6),
                          MasterZone(msgs[9], 7),
                          MasterFooterBlock(msgs[10], master_id),
        )

    @staticmethod
    def get_nb_blocks():
        return 11

    @staticmethod
    def get_dump_request(master_id):
        if master_id < 0 or master_id > 127:
            return Dump.get_dump_request((0x0E, 0x7F, 0))
        else:
            return Dump.get_dump_request((0x0E, 0x70, master_id))

    def get_dump(self):
        return map(lambda block: block.get_msg(), self.blocks)

    def get_master_id(self):
        return self.blocks[0].get_master_id()

    def get_common_block(self):
        return self.blocks[1]

    def get_zone_block(self, zone):
        if zone < 0 or zone > 7:
            raise ValueError("Invalid Zone id")
        return self.blocks[2 + zone]

    def print(self):
        print("== Master Dump ==")
        for block in self.blocks:
            block.print()
            print('---')
