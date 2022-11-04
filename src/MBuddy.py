import ui
import os
from MixView import MixView
from MidiSocket import MidiSocket
import SelectBox
import State

COLOR_RED = (1, 0, 0, 0.5)
COLOR_GREEN = (0, 1, 0, 0.5)

BUTTON_PREV = 0x70
BUTTON_NEXT = 0x71
BUTTON_STOP = 0x72
BUTTON_PLAY = 0x73
BUTTON_LOOP = 0x74
BUTTON_REC = 0x75


def current_stage_index():
    try:
        return stage_order.index(current_song_id)
    except ValueError:
        return -1


def on_mix_button_pressed(sender):
    print('Mix settings disabled')
    # v = MixView(state=current_state)
    # v.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
    # v.wait_modal()
    # current_state.to_file(filepath=preset_path(current_song_id))


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
    if stage_index >= 0:
        load_song(stage_order[max(0, stage_index - 1)])


def on_next_button_pressed(sender):
    stage_index = current_stage_index()
    if stage_index >= 0:
        load_song(stage_order[min(len(stage_order) - 1, stage_index + 1)])


def on_title_button_pressed(sender):
    global current_song_id, scores, stage_order
    stop_midi_loop()

    ordered_scores = []
    for song_id in stage_order:
        ordered_scores.append(scores[song_id])
    (ordered_id, ordered_scores) = SelectBox.select(ordered_scores, current_stage_index(), True)

    stage_order = []
    for song_name in ordered_scores:
        stage_order.append(scores.index(song_name))

    with open('stage_order.py', 'w') as file:
        file.write(str(stage_order))

    if ordered_id >= 0:
        load_song(stage_order[ordered_id])

    start_midi_loop()


def on_midi_message(msg):
    # CC message
    if len(msg) != 3 or msg[0] & 0xF0 != 0xB0:
        return

    # Button must be pressed
    if msg[2] == 0:
        return

    if msg[1] == BUTTON_PREV:
        on_previous_button_pressed(None)
    elif msg[1] == BUTTON_NEXT:
        on_next_button_pressed(None)
    elif msg[1] == BUTTON_STOP:
        load_song(stage_order[0])


@ui.in_background
def midi_loop():
    global midi_socket, is_midi_loop_running
    midi_socket.poll_input()
    if is_midi_loop_running:
        ui.delay(midi_loop, 0.1)


is_midi_loop_running = False


def start_midi_loop():
    global is_midi_loop_running
    is_midi_loop_running = True
    midi_loop()


def stop_midi_loop():
    global is_midi_loop_running
    is_midi_loop_running = False
    ui.cancel_delays()


@ui.in_background
def load_song(new_song_id):
    global current_song_id, scores, midi_socket
    if new_song_id < 0 or new_song_id >= len(scores) or new_song_id == current_song_id:
        return

    spinner = ui.ActivityIndicator()
    spinner.style = ui.ACTIVITY_INDICATOR_STYLE_GRAY
    spinner.center = mbuddy.center
    spinner.start()
    mbuddy.add_subview(spinner)

    current_song_id = new_song_id
    midi_socket.send_song_change(current_song_id)

    mbuddy['score_image'].image = ui.Image.named('scores/' + scores[current_song_id])
    mbuddy['info_label'].text = '  ' + scores[current_song_id][:-4]
    spinner.stop()
    mbuddy.remove_subview(spinner)

class MyView (ui.View):
    def will_close(self):
        stop_midi_loop()


mbuddy = ui.load_view()
midi_socket = MidiSocket(on_error=on_network_error, on_midi_event=on_midi_message)
scores = sorted(os.listdir('scores'))
current_song_id = -1

stage_order = []
if os.path.exists('stage_order.py'):
    with open('stage_order.py') as file:
        stage_order = eval(file.read())
if len(stage_order) != len(scores):
    stage_order = range(0, len(scores))

load_song(stage_order[0])
start_midi_loop()


mbuddy.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
