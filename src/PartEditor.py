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

        return instance

    def on_voice_button_pressed(self, sender):

        def send_midi_message(voice_id):
            self.state.set_voice(voice_id)

        v = VoicePicker.load_view(on_changed=send_midi_message, current=self.state.voice())
        v.present('sheet')
        v.wait_modal()
        self.__update_voice_name()

    def __update_voice_name(self):
        (msb, lsb, prg) = self.state.voice()
        voice = motif.id2voices[msb][lsb][prg]
        self['voice_label'].text = '  [' + voice[0] + ']  [' + voice[1] + "]\n  " + voice[2]


if __name__ == '__main__':
    v = PartEditor.load_view(state=State.PartState.from_default(0))
    v.present('sheet')
