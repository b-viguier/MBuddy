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

    def size(self):
        return  self.msg[5] * 128 + self.msg[6]

    def get_data(self, offset, size=1):
        data_size = self.size()
        if not (0 <= offset < data_size) or not (0 <= (offset + size - 1) < data_size):
            raise ValueError("Invalid data read")
        data = self.msg[10 + offset:10 + offset + size]
        return data[0] if size == 1 else tuple(data)

    def set_data(self, offset, data):
        data_size = self.size()
        if not (0 <= offset < data_size):
            raise ValueError("Invalid data write")
        if type(data) == int:
            if not 0 <= data <= 127:
                raise ValueError("Invalid data write value")
            self.msg[10 + offset] = data
        else:
            if not 0 <= (offset + len(data) - 1) < data_size:
                raise ValueError("Invalid data write length")
            for index, value in enumerate(data):
                if not 0 <= value <= 127:
                    raise ValueError("Invalid data write value")
                self.msg[10 + offset + index] = value

    def set_data_boolean_mask(self, offset, mask, state):
        current = self.get_data(offset)
        self.set_data(offset, mask | current if state else (mask ^ 0b11111111) & current)

    def get_msg(self):
        checksum = 128 - sum(self.msg[5:-2]) % 128
        self.msg[-2] = checksum
        return self.msg


class MasterHeaderBlock(Block):
    def __init__(self, msg, master_id):
        if 0 <= master_id <= 127:
            super().__init__(msg, (0x0E, 0x70, master_id), 0)
        else:
            super().__init__(msg, (0x0E, 0x7F, 0), 0)

    def get_master_id(self):
        return self.address()[2]

    def print(self):
        print({
            'master_id': self.get_master_id(),
        })


class MasterFooterBlock(Block):
    def __init__(self, msg, master_id):
        if 0 <= master_id <= 127:
            super().__init__(msg, (0x0F, 0x70, master_id), 0)
        else:
            super().__init__(msg, (0x0F, 0x7F, 0), 0)

    def print(self):
        print('end')


class MasterCommonBlock(Block):
    def __init__(self, msg):
        super().__init__(msg, (0x33, 0x00, 0x00), 0x1F)

    def get_title(self):
        return ''.join(map(chr, self.get_data(0x00, 20))).split('\x00', 1)[0]

    def set_title(self, title):
        self.set_data(0x00, title.ljust(20, '\x00').encode()[0:20])
        return self

    def get_mode(self):
        return Motif.MasterMode(self.get_data(0x19))

    def set_mode(self, mode):
        return self.get_data(0x19, Motif.MasterMode(mode))

    def get_bank_program(self):
        return self.get_data(0x1A, 3)

    def set_bank_program(self, voice):
        if len(voice) != 3:
            raise ValueError("Invalid bank/program")
        self.set_data(0x1A, voice)
        return self

    def are_zones_enabled(self):
        return True if self.get_data(0x1D) else False

    def enable_zones(self, state):
        self.set_data(0x1D, 0x1 if state else 0x0)
        return self

    def print(self):
        print({
            'title': self.get_title(),
            'mode': self.get_mode(),
            'bank_program': self.get_bank_program(),
            'zones_enabled': self.are_zones_enabled(),
        })


class MasterZone(Block):
    def __init__(self, msg, zone_id):
        super().__init__(msg, (0x32, zone_id, 0x00), 0x10)

    def get_zone_id(self):
        return self.address()[1]

    def get_transmit_channel(self):
        return 0b00001111 & self.get_data(0x0)

    def set_transmit_channel(self, channel):
        if not 0 <= channel < 16:
            raise ValueError("Invalid Midi Channel")
        self.set_data(0, (self.get_data(0x0) & 0b11110000) | channel)
        return self

    def is_transmit_enabled(self):
        return True if 0b00010000 & self.get_data(0x0) else False

    def enable_transmit(self, state):
        self.set_data_boolean_mask(0x0, 0b00010000, state)
        return self

    def is_internal_enabled(self):
        return True if 0b00100000 & self.get_data(0x0) else False

    def enable_internal(self, state):
        self.set_data_boolean_mask(0x0, 0b00100000, state)
        return self

    def get_transpose_octave(self):
        return self.get_data(0x1) - 0x40

    def set_transpose_octave(self, shift):
        self.set_data(0x1, 0x40 + shift)
        return self

    def get_transpose_semitone(self):
        return self.get_data(0x2) - 0x40

    def set_transpose_semitone(self, shift):
        self.set_data(0x2, shift + 0x40)
        return self

    def get_note_low(self):
        return self.get_data(0x3)

    def set_note_low(self, note):
        self.set_data(0x3, note)
        return self

    def get_note_high(self):
        return self.get_data(0x4)

    def set_note_high(self, note):
        self.set_data(0x4, note)
        return self

    def get_volume(self):
        return self.get_data(0x6)

    def set_volume(self, volume):
        self.set_data(0x6, volume)
        return self

    def get_pan(self):
        return self.get_data(0x7)

    def set_pan(self, pan):
        self.set_data(0x7, pan)
        return self

    def get_bank_program(self):
        return self.get_data(0x8, 3)

    def set_bank_program(self, voice):
        if len(voice) != 3:
            raise ValueError("Invalid bank/program")
        self.set_data(0x3, voice)
        return self

    def is_internal_bank_select_enabled(self):
        return True if self.get_data(0xB) & 0b00000001 else False

    def enable_internal_bank_select(self, state):
        self.set_data_boolean_mask(0xB, 0b00000001, state)
        return self

    def is_internal_program_change_enabled(self):
        return True if self.get_data(0xB) & 0b00000010 else False

    def enable_internal_program_change(self, state):
        self.set_data_boolean_mask(0xB, 0b00000010, state)
        return self

    def is_transmit_bank_select_enabled(self):
        return True if self.get_data(0xB) & 0b00001000 else False

    def enable_transmit_bank_select(self, state):
        self.set_data_boolean_mask(0xB, 0b00001000, state)
        return self

    def is_transmit_program_change_enabled(self):
        return True if self.get_data(0xB) & 0b00010000 else False

    def enable_transmit_program_change(self, state):
        self.set_data_boolean_mask(0xB, 0b00010000, state)
        return self

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
        if 0 <= master_id <= 127:
            return Dump.get_dump_request((0x0E, 0x70, master_id))
        else:
            return Dump.get_dump_request((0x0E, 0x7F, 0))

    def get_dump(self):
        return map(lambda block: block.get_msg(), self.blocks)

    def get_master_id(self):
        return self.blocks[0].get_master_id()

    def get_common_block(self):
        return self.blocks[1]

    def get_zone_block(self, zone):
        if not 0 <= zone <= 7:
            raise ValueError("Invalid Zone id")
        return self.blocks[2 + zone]

    def print(self):
        print("== Master Dump ==")
        for block in self.blocks:
            block.print()
            print('---')
