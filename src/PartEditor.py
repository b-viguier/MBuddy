import ui
from VoicePicker import VoicePicker
import motif
import SelectBox
import State


class PartEditor(ui.View):
    def __init__(self, *args, **kwargs):
        super(PartEditor, self).__init__(args, kwargs)
        self.state = None

    @staticmethod
    def load_view(*, state):
        instance = ui.load_view()
        instance.state = state
        instance['channel_label'].text = str(state.channel()+1)

        instance.__update_voice_name()
        instance['voice_button'].action = instance.on_voice_button_pressed

        instance['volume_slider'].value = instance.state.volume() / 127
        instance['volume_slider'].action = instance.on_volume_slider_changed

        instance['note_upper_button'].title = PartEditor.__midi_note_to_name(instance.state.upper_note())
        instance['note_upper_button'].action = instance.on_upper_note_pressed

        instance['note_lower_button'].title = PartEditor.__midi_note_to_name(instance.state.lower_note())
        instance['note_lower_button'].action = instance.on_lower_note_pressed

        instance['tune_button'].title = str(instance.state.tune())
        instance['tune_button'].action = instance.on_tune_pressed

        instance['enable_switch'].value = instance.state.enabled()
        instance['enable_switch'].action = instance.on_enabled
        instance.on_enabled(instance['enable_switch'])

        return instance

    def on_voice_button_pressed(self, sender):

        def send_midi_message(voice_id):
            self.state.set_voice(voice_id)

        v = VoicePicker.load_view(on_changed=send_midi_message, current=self.state.voice())
        v.present('sheet')
        v.wait_modal()
        self.__update_voice_name()

    def on_volume_slider_changed(self, sender):
        self.state.set_volume(round(sender.value * 127))

    def on_lower_note_pressed(self, sender):
        new_note = PartEditor.__select_note(self.state.lower_note())
        self['note_lower_button'].title = PartEditor.__midi_note_to_name(new_note)
        self.state.set_lower_note(new_note)

    def on_upper_note_pressed(self, sender):
        new_note = PartEditor.__select_note(self.state.upper_note())
        self['note_upper_button'].title = PartEditor.__midi_note_to_name(new_note)
        self.state.set_upper_note(new_note)

    def on_tune_pressed(self, sender):
        new_tune = SelectBox.select(range(-24, 25), self.state.tune() + 24)[0] - 24
        self.state.set_tune(new_tune)
        self['tune_button'].title = str(new_tune)

    def on_enabled(self, sender):
        hidden = not sender.value
        self['voice_button'].hidden = hidden
        self['volume_slider'].hidden = hidden
        self['note_lower_button'].hidden = hidden
        self['note_upper_button'].hidden = hidden
        self['tune_button'].hidden = hidden
        self['voice_label'].hidden = hidden
        self.state.set_enabled(sender.value)

    def __update_voice_name(self):
        (msb, lsb, prg) = self.state.voice()
        voice = motif.id2voices[msb][lsb][prg]
        self['voice_label'].text = '  [' + voice[0] + ']  [' + voice[1] + "]\n  " + voice[2]

    @staticmethod
    def __select_note(current_midi_note):
        named_notes = PartEditor.__named_notes()
        list_id = SelectBox.select(named_notes, current_midi_note - State.PartState.MIDI_NOTE_C2)[0]
        return State.PartState.MIDI_NOTE_C2 + list_id

    @staticmethod
    def __named_notes():
        return [
                   note + str(octave)
                   for octave in range(2, 7)
                   for note in ['C', 'C#', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'Ab', 'A', 'Bb', 'B']
               ] + ['C7']

    @staticmethod
    def __midi_note_to_name(note_id):
        return PartEditor.__named_notes()[note_id - State.PartState.MIDI_NOTE_C2]


if __name__ == '__main__':
    v = PartEditor.load_view(state=State.PartState.from_default(0))
    v.present('sheet')
