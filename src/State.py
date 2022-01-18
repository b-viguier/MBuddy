class PartState(object):
    MIDI_NOTE_C2 = 36
    MIDI_NOTE_C7 = 96

    def __init__(self, dict):
        self.data = dict

    @staticmethod
    def from_default(channel):
        s = PartState({'channel': channel})
        s.set_enabled(True)
        s.set_voice((0, 0, 0))
        s.set_volume(115)
        s.set_lower_note(PartState.MIDI_NOTE_C2)
        s.set_upper_note(PartState.MIDI_NOTE_C7)
        s.set_tune(0)
        return s

    def to_dict(self):
        return self.data

    def channel(self):
        return self.data['channel']

    def enabled(self):
        return self.data['enabled']

    def set_enabled(self, enabled):
        self.data['enabled'] = enabled

    def voice(self):
        return self.data['voice']

    def set_voice(self, voice):
        self.data['voice'] = voice

    def volume(self):
        return self.data['volume']

    def set_volume(self, volume):
        self.data['volume'] = volume

    def lower_note(self):
        return self.data['lower_note']

    def set_lower_note(self, lower_note):
        self.data['lower_note'] = lower_note

    def upper_note(self):
        return self.data['upper_note']

    def set_upper_note(self, upper_note):
        self.data['upper_note'] = upper_note

    def tune(self):
        return self.data['tune']

    def set_tune(self, tune):
        self.data['tune'] = tune


class SongState(object):
    def __init__(self, dict):
        self.data = dict

    @staticmethod
    def from_file(*, filepath):
        with open(filepath) as file:
            dict = eval(file.read())
        for channel in range(0,8):
            key = 'part_' + str(channel)
            dict[key] = PartState(dict[key])
        return SongState(dict)

    def to_file(self, *, filepath):
        d = dict(self.data)
        for channel in range(0, 8):
            key = 'part_' + str(channel)
            d[key] = d[key].to_dict()
        with open(filepath, 'w') as file:
            file.write(str(d))

    @staticmethod
    def from_default():
        dict = {}
        dict['part_0'] = PartState.from_default(0)
        dict['part_4'] = PartState.from_default(4)
        for channel in (1, 2, 3, 5, 6, 7):
            s = PartState.from_default(channel)
            s.set_enabled(False)
            dict['part_' + str(channel)] = s
        return SongState(dict)

    def part(self, channel):
        return self.data['part_' + str(channel)]


class MidiPartState(PartState):
    def __init__(self, midi, dict):
        super(MidiPartState, self).__init__(dict)
        self.midi = midi

        # Send parameters not stored in Master
        channel = self.channel()
        if channel >= 4:
            self.midi.send_enabled(channel, self.enabled())
            if self.enabled():
                self.midi.send_volume_change(self.channel(), self.volume())
                self.midi.send_lower_note(channel, self.lower_note())
                self.midi.send_upper_note(channel, self.upper_note())
                self.midi.send_note_shift(channel, self.tune())
                self.midi.send_program_change(channel, self.voice())

    def set_enabled(self, enabled):
        super().set_enabled(enabled)
        self.midi.send_enabled(self.channel(), self.enabled())

    def set_voice(self, voice):
        super().set_voice(voice)
        self.midi.send_program_change(self.channel(), self.voice())

    def set_volume(self, volume):
        super().set_volume(volume)
        self.midi.send_volume_change(self.channel(), self.volume())

    def set_lower_note(self, lower_note):
        super().set_lower_note(lower_note)
        self.midi.send_lower_note(self.channel(), self.lower_note())

    def set_upper_note(self, upper_note):
        super().set_upper_note(upper_note)
        self.midi.send_upper_note(self.channel(), self.upper_note())

    def set_tune(self, tune):
        super().set_tune(tune)
        self.midi.send_note_shift(self.channel(), self.tune())


class MidiSongState(SongState):
    def __init__(self, midi, dict):
        super(MidiSongState, self).__init__(dict)
        self.midi = midi

    @staticmethod
    def from_song_state(*, song_state, midi):
        dict = song_state.data
        for channel in range(0, 8):
            key = 'part_' + str(channel)
            dict[key] = MidiPartState(midi, dict[key].to_dict())
        return MidiSongState(midi, dict)
