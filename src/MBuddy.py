import ui
import os
from MixView import MixView
from MidiSocket import MidiSocket
import SelectBox
import State

COLOR_RED = (1, 0, 0, 0.5)
COLOR_GREEN = (0, 1, 0, 0.5)

stageOrder = (
    1,  # I Wish
    10, # Love Foolosophy
    20, # Street Life
    2,  # Too Young to Die
    9,  # Midnight
    15, # 1975
    16, # Get Into My Groove
    24, # Georgy Porgy
    23, # Somebody else's Guy
    21, # Work to do
    22, # Never Too Much
    7,  # Good Times
    13, # Love Got in the Way
    4,  # Sledgehammer
    12, # I feel it commin'
)


def current_stage_index():
    try:
        return stageOrder.index(current_song_id+1)
    except ValueError:
        return -1


def on_mix_button_pressed(sender):
    v = MixView(state=current_state)
    v.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
    v.wait_modal()
    current_state.to_file(filepath=preset_path(current_song_id))


def on_network_button_pressed(sender):
    global midi_socket
    if midi_socket.is_blocked():
        mbuddy['network_button'].background_color = COLOR_GREEN
        midi_socket.try_reconnect()
    else:
        mbuddy['network_button'].background_color = COLOR_RED
        midi_socket.disconnect()


def on_network_error(error):
    print(error)
    mbuddy['network_button'].background_color = COLOR_RED


def on_previous_button_pressed(sender):
    stage_index = current_stage_index()
    load_song(current_song_id - 1 if stage_index == -1 else stageOrder[max(0, stage_index - 1)]-1)


def on_next_button_pressed(sender):
    stage_index = current_stage_index()
    load_song(current_song_id + 1 if stage_index == -1 else stageOrder[min(len(stageOrder) - 1, stage_index + 1)]-1)


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
midi_socket = MidiSocket(on_error=on_network_error)
scores = sorted(os.listdir('scores'))
current_state = State.SongState.from_default()
current_song_id = -1
load_song(0)


mbuddy.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
