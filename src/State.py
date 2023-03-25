class PartState(object):

    def __init__(self, dict):
        self.data = dict

    @staticmethod
    def from_default(channel):
        s = PartState({'channel': channel})
        s.set_voice((0, 0, 0))
        s.set_volume(110)
        return s

    @staticmethod
    def from_invalid(channel):
        s = PartState({'channel': channel})
        s.set_voice((-1, -1, -1))
        s.set_volume(-1)
        return s

    def is_valid(self):
        (msb, lsb, pc) = self.voice()
        return msb >= 0 and lsb >= 0 and pc >= 0 and self.volume() >= 0

    def to_dict(self):
        return self.data

    def channel(self):
        return self.data['channel']

    def voice(self):
        return self.data['voice']

    def set_voice(self, voice):
        self.data['voice'] = voice

    def volume(self):
        return self.data['volume']

    def set_volume(self, volume):
        self.data['volume'] = volume


class SongState(object):
    def __init__(self, dict):
        self.data = dict

    @staticmethod
    def from_default():
        data = {}
        for channel in range(0, 16):
            data[channel] = PartState.from_default(channel)
        return SongState(data)

    @staticmethod
    def from_invalid():
        data = {}
        for channel in range(0, 16):
            data[channel] = PartState.from_invalid(channel)
        return SongState(data)

    def is_valid(self):
        for channel in range(0, 16):
            if not self.data[channel].is_valid():
                return False
        return True

    def part(self, channel):
        return self.data[channel]


class MidiPartState(PartState):
    def __init__(self, midi, dict):
        super(MidiPartState, self).__init__(dict)
        self.midi = midi

    def set_voice(self, voice):
        super().set_voice(voice)
        self.midi.send_program_change(self.channel(), self.voice())

    def set_volume(self, volume):
        super().set_volume(volume)
        self.midi.send_volume_change(self.channel(), self.volume())

    def send(self):
        self.midi.send_program_change(self.channel(), self.voice())
        self.midi.send_volume_change(self.channel(), self.volume())


class MidiSongState(SongState):
    def __init__(self, midi, data):
        super(MidiSongState, self).__init__(data)
        self.midi = midi

    @staticmethod
    def from_song_state(*, song_state, midi):
        data = song_state.data
        for channel in range(0, 16):
            data[channel] = MidiPartState(midi, data[channel].to_dict())
        return MidiSongState(midi, data)

    def send(self):
        for channel in range(0, 16):
            self.data[channel].send()
