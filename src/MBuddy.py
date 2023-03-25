import ui
import os
import log
from MixView import MixView
import MidiSocket as ms
import SelectBox
import ConfirmBox
import State

COLOR_RED = (1, 0, 0, 0.5)
COLOR_GREEN = (0, 1, 0, 0.5)


def current_stage_index():
    try:
        return stage_order.index(current_song_id)
    except ValueError:
        return -1


@ui.in_background
def on_mix_button_pressed(sender):
    def then(state):
        if state is not None:
            stop_midi_loop()

            v = MixView(state=State.MidiSongState.from_song_state(midi=midi_socket, song_state=state))
            v.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
            v.wait_modal()

            start_midi_loop()
    retrieve_state(then)


def on_network_button_pressed(sender):
    global midi_socket
    if midi_socket.is_blocked():
        mbuddy['network_button'].background_color = COLOR_GREEN
        midi_socket.try_reconnect()
    else:
        mbuddy['network_button'].background_color = COLOR_RED
        midi_socket.disconnect()


def on_panic_button_pressed(sender):
    global midi_socket
    midi_socket.send_panic()


def on_network_error(error):
    log.dbg(repr(error))
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


def on_impulse_button_pressed(button):
    global midi_socket, stage_order
    if button == ms.BUTTON_PREV:
        on_previous_button_pressed(None)
    elif button == ms.BUTTON_NEXT:
        on_next_button_pressed(None)
    elif button == ms.BUTTON_STOP:
        load_song(stage_order[0])


def on_volume_param(channel, volume):
    global midi_socket, current_state
    current_state.part(channel).set_volume(volume)


def on_voice_param(channel, voice):
    global midi_socket, current_state
    part_state = current_state.part(channel)
    current_voice = part_state.voice()
    part_state.set_voice((max(voice[0], current_voice[0]), max(voice[1], current_voice[1]), max(voice[2], current_voice[2])))


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


@ui.in_background
def on_archive_button_pressed(sender):
    def then(state):
        if state is None:
            return
        stop_midi_loop()
        if ConfirmBox.ask("Send current state?"):
            midi_state = State.MidiSongState.from_song_state(midi=midi_socket, song_state=state)
            midi_state.send()
        start_midi_loop()
    retrieve_state(then)


def retrieve_state(then):
    global midi_socket, current_state
    spinner = ui.ActivityIndicator()
    spinner.style = ui.ACTIVITY_INDICATOR_STYLE_GRAY
    spinner.center = mbuddy.center
    spinner.start()
    mbuddy.add_subview(spinner)

    stop_midi_loop()
    current_state = State.SongState.from_invalid()
    for channel in range(0, 16):
        midi_socket.request_current_volume(channel)
        midi_socket.request_current_voice(channel)
    start_midi_loop()

    def after_delay():
        spinner.stop()
        mbuddy.remove_subview(spinner)

        if current_state.is_valid():
            log.dbg('Valid state')
            then(current_state)
        else:
            log.dbg('Invalid state')
            then(None)
    ui.delay(after_delay, 3)


def on_log_button_pressed(sender):
    global mbuddy
    if mbuddy['logview'] is None:
        mbuddy.add_subview(ui.TextView(frame=(0, 60, 200, 900), name='logview'))
        mbuddy['logview'].editable = False
        mbuddy['logview'].border_width = 2

        def print_in_view(msg):
            global mbuddy
            mbuddy['logview'].text = mbuddy['logview'].text + msg + "\n"

        log.set_callback(print_in_view)
    else:
        mbuddy.remove_subview(mbuddy['logview'])
        log.set_callback(None)


class MyView (ui.View):
    def will_close(self):
        stop_midi_loop()


mbuddy = ui.load_view()
midi_socket = ms.MidiSocket(
    on_error=on_network_error,
    on_impulse_event=on_impulse_button_pressed,
    on_volume_param=on_volume_param,
    on_voice_param=on_voice_param,
)
scores = sorted(os.listdir('scores'))
current_song_id = -1
current_state = None

stage_order = []
if os.path.exists('stage_order.py'):
    with open('stage_order.py') as file:
        stage_order = eval(file.read())
if len(stage_order) != len(scores):
    stage_order = range(0, len(scores))

load_song(stage_order[0])
start_midi_loop()


mbuddy.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
