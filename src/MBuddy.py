import ui
import os
from MixView import MixView
from MidiSocket import MidiSocket
import SelectBox
import State


def on_mix_button_pressed(sender):
    v = MixView(state=current_state)
    v.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
    v.wait_modal()
    current_state.to_file(filepath=preset_path(current_song_id))


def on_previous_button_pressed(sender):
    load_song(current_song_id - 1)


def on_next_button_pressed(sender):
    load_song(current_song_id + 1)


def on_title_button_pressed(sender):
    global current_song_id, scores
    load_song(SelectBox.select(scores, current_song_id))

@ui.in_background
def load_song(new_song_id):
    global current_song_id, scores, current_state, midi_socket
    if new_song_id < 0 or new_song_id >= len(scores) or new_song_id == current_song_id:
        return

    spinner = ui.ActivityIndicator()
    spinner.style = ui.ACTIVITY_INDICATOR_STYLE_GRAY
    spinner.center = mbuddy.center
    spinner.start()
    mbuddy.add_subview(spinner)
    path = preset_path(new_song_id)
    if os.path.isfile(path):
        state = State.SongState.from_file(filepath=path)
    else:
        state = State.SongState.from_default()

    current_song_id = new_song_id
    midi_socket.send_song_change(current_song_id)
    
    current_state = State.MidiSongState.from_song_state(song_state=state, midi=midi_socket)


    mbuddy['score_image'].image = ui.Image.named('scores/' + scores[current_song_id])
    mbuddy['info_label'].text = '  ' + scores[current_song_id][:-4]
    spinner.stop()
    mbuddy.remove_subview(spinner)


def preset_path(song_id):
    return 'presets/' + str(song_id) + '.py'


mbuddy = ui.load_view()
midi_socket = MidiSocket()
scores = sorted(os.listdir('scores'))
current_state = State.SongState.from_default()
current_song_id = -1
load_song(0)


mbuddy.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
