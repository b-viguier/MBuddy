import socket
from time import sleep

BUTTON_PREV = 0x70
BUTTON_NEXT = 0x71
BUTTON_STOP = 0x72
BUTTON_PLAY = 0x73
BUTTON_LOOP = 0x74
BUTTON_REC = 0x75


class MidiSocket(object):
    def __init__(self, port_in=8321, port_out=8123, on_error=None, on_impulse_event=None, on_volume_param=None, on_voice_param=None):
        self.__socket_input = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self.__socket_output = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self.__on_error = on_error
        self.__on_impulse_event = on_impulse_event
        self.__on_volume_param = on_volume_param
        self.__on_voice_param = on_voice_param
        self.__is_blocked = False

        if self.__on_error is None:
            def on_error(reason):
                print(reason)
            self.__on_error = on_error

        if self.__on_impulse_event is None:
            def on_impulse_event(btn):
                print(btn)
            self.__on_impulse_event = on_impulse_event

        if self.__on_volume_param is None:
            def on_volume_param(channel, volume):
                print(channel, volume)
            self.__on_volume_param = on_volume_param

        if self.__on_voice_param is None:
            def on_voice_param(channel, voice_id):
                print(channel, voice_id)
            self.__on_voice_param = on_voice_param

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
        self.__send_motif_param([0x37, channel, 0x01], [voice_id[0]])
        self.__send_motif_param([0x37, channel, 0x02], [voice_id[1]])
        self.__send_motif_param([0x37, channel, 0x03], [voice_id[2]])

    def send_volume_change(self, channel, volume):
        self.__send_motif_param([0x37, channel, 0x0E], [volume])

    def request_current_volume(self, channel):
        self.__request_motif_param([0x37, channel, 0x0E])

    def request_current_voice(self, channel):
        self.__request_motif_param([0x37, channel, 0x01])
        self.__request_motif_param([0x37, channel, 0x02])
        self.__request_motif_param([0x37, channel, 0x03])

    def send_song_change(self, song_id):
        # Change current master
        self.__send_motif_param([0x0A, 0x00, 0x00], [song_id])
        sleep(1)    # To be sure the song is loaded before to trigger next event
        # Trigger "Play" so the Motif's sequencer sends Sysex to Impulse
        self.__send_midi([0xFA])

    def send_panic(self):
        CHANNEL_MODE_MSG = 0xB0
        for channel in range(0, 16):
            # All Notes Off
            self.__send_midi([CHANNEL_MODE_MSG + channel, 0x7B, 0x00])
            # All Sounds Off
            self.__send_midi([CHANNEL_MODE_MSG + channel, 0x78, 0x00])

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

    def __request_motif_param(self, address):
        self.__send_midi([0xF0, 0x43, 0x30, 0x7F, 0x03, *address, 0xF7])

    def __on_midi_event(self, msg):
        # CC message
        if len(msg) == 3 and msg[0] & 0xF0 == 0xB0:
            # Button must be pressed
            if msg[2] == 0:
                return

            if msg[1] in (BUTTON_PREV, BUTTON_NEXT, BUTTON_STOP, BUTTON_REC, BUTTON_PLAY, BUTTON_LOOP):
                self.__on_impulse_event(msg[1])

        # Data synchronization
        elif msg[:5] == [0xF0, 0x43, 0x10, 0x7F, 0x03]:
            # Mix
            if msg[5] == 0x37:
                channel = msg[6]
                # Volume
                if msg[7] == 0x0E:
                    volume = msg[8]
                    self.__on_volume_param(channel, volume)

                # Voice
                elif msg[7] == 0x01:
                    msb = msg[8]
                    self.__on_voice_param(channel, (msb, -1, -1))
                elif msg[7] == 0x02:
                    lsb = msg[8]
                    self.__on_voice_param(channel, (-1, lsb, -1))
                elif msg[7] == 0x03:
                    pc = msg[8]
                    self.__on_voice_param(channel, (-1, -1, pc))


if __name__ == '__main__':
    midi = MidiSocket()
    midi.send_program_change(2, (63, 1, 1))
    midi.send_volume_change(2, 122)

    midi.request_current_volume(0)
    midi.request_current_voice(0)

    while True:
        midi.poll_input()
        sleep(0.1)
