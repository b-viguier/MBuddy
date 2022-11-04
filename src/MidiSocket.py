import socket
from time import sleep


class MidiSocket(object):
    def __init__(self, port_in=8321, port_out=8123, on_error=None, on_midi_event=None):
        self.__socket_input = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self.__socket_output = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self.__on_error = on_error
        self.__on_midi_event = on_midi_event
        self.__is_blocked = False

        if self.__on_error is None:
            def on_error(reason):
                print(reason)
            self.__on_error = on_error

        if self.__on_midi_event is None:
            def on_midi_event(msg):
                print(msg)
            self.__on_midi_event = on_midi_event

        IPADDR = '127.0.0.1'
        self.__socket_input.bind((IPADDR, port_in))
        self.__socket_input.settimeout(0.05)
        self.__socket_output.connect((IPADDR, port_out))

    def __del__(self):
        self.__socket_output.close()
        self.__socket_input.close()

    def poll_input(self):
        try:
            for i in range(0, 10):
                bytes, ip = self.__socket_input.recvfrom(4096)
                self.__on_midi_event(list(bytes))
        except socket.timeout:
            pass

    def is_blocked(self):
        return self.__is_blocked

    def try_reconnect(self):
        self.__is_blocked = False

    def disconnect(self):
        self.__is_blocked = True

    def send_program_change(self, channel, voice_id):
        # Master Init value
        self.__send_motif_param([0x32, channel, 0x08], [voice_id[0]])
        self.__send_motif_param([0x32, channel, 0x09], [voice_id[1]])
        self.__send_motif_param([0x32, channel, 0x0A], [voice_id[2]])
        # Mix
        self.__send_motif_param([0x37, channel, 0x01], [voice_id[0]])
        self.__send_motif_param([0x37, channel, 0x02], [voice_id[1]])
        self.__send_motif_param([0x37, channel, 0x03], [voice_id[2]])

    def send_volume_change(self, channel, volume):
        self.__send_motif_param([0x32, channel, 0x06], [volume])  # Master Init value
        self.__send_motif_param([0x37, channel, 0x0E], [volume])  # Mix

    def send_song_change(self, song_id):
        self.__send_motif_param([0x0A, 0x00, 0x00], [song_id])
        sleep(1)    # To be sure the song is loaded before to trigger next event
        self.__send_midi([0xFA])

    def send_lower_note(self, channel, note):
        if channel < 4:
            self.__send_motif_param([0x32, channel, 0x03], [note])
        else:
            self.__send_motif_param([0x37, channel, 0x08], [note])

    def send_upper_note(self, channel, note):
        if channel < 4:
            self.__send_motif_param([0x32, channel, 0x04], [note])
        else:
            self.__send_motif_param([0x37, channel, 0x09], [note])

    def send_note_shift(self, channel, shift):
        if channel < 4:
            self.__send_motif_param([0x32, channel, 0x01], [64 + int(shift / 12)])  # octave
            self.__send_motif_param([0x32, channel, 0x02], [64 + (shift % 12 if shift >= 0 else -((-shift) % 12))])  # semitone
        else:
            self.__send_motif_param([0x37, channel, 0x17], [64 + shift])

    def send_enabled(self, channel, enabled):
        if channel < 4:
            self.__send_motif_param([0x32, channel, 0x00], [channel | (0x20 if enabled else 0x00)])
        else:
            self.__send_motif_param([0x37, channel, 0x04], [8 if enabled else 0x7F])

    def __send_midi(self, msg):
        if self.__is_blocked:
            return
        try:
            self.__socket_output.send(bytearray(msg))
            sleep(0.01)
        except Exception as e:
            self.__is_blocked = True
            self.__on_error(e)

    def __send_motif_param(self, address, value):
        self.__send_midi([0xF0, 0x43, 0x10, 0x7F, 0x03, *address, *value, 0xF7])


if __name__ == '__main__':
    midi = MidiSocket()
    midi.send_program_change(2, (63, 1, 1))
    midi.send_volume_change(2, 122)

    while True:
        midi.poll_input()
        sleep(0.1)
