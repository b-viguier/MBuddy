import socket
from time import sleep

class MidiSocket(object):
    def __init__(self, port_in=8321, port_out=8123):
        self.socket_input = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self.socket_output = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)

        IPADDR = '127.0.0.1'
        self.socket_input.bind((IPADDR, port_in))
        self.socket_output.connect((IPADDR, port_out))

    def __del__(self):
        self.socket_output.close()
        self.socket_input.close()

    def send_program_change(self, channel, voice_id):
        # Master Init value
        self.__sendMotifParam([0x32, channel, 0x08], [voice_id[0]])
        self.__sendMotifParam([0x32, channel, 0x09], [voice_id[1]])
        self.__sendMotifParam([0x32, channel, 0x0A], [voice_id[2]])
        # Mix
        self.__sendMotifParam([0x37, channel, 0x01], [voice_id[0]])
        self.__sendMotifParam([0x37, channel, 0x02], [voice_id[1]])
        self.__sendMotifParam([0x37, channel, 0x03], [voice_id[2]])

    def on_program_change(self, channel, callback):
        pass

    def send_volume_change(self, channel, volume):
        self.__sendMotifParam([0x32, channel, 0x06], [volume])  # Master Init value
        self.__sendMotifParam([0x37, channel, 0x0E], [volume])  # Mix

    def on_volume_change(self, channel, callback):
        pass

    def send_song_change(self, song_id):
        self.__sendMotifParam([0x0A, 0x00, 0x00], [song_id])
        sleep(0.5)

    def on_song_change(self, callback):
        pass

    def send_lower_note(self, channel, note):
        if channel < 4:
            self.__sendMotifParam([0x32, channel, 0x03], [note])
        else:
            self.__sendMotifParam([0x37, channel, 0x08], [note])

    def send_upper_note(self, channel, note):
        if channel < 4:
            self.__sendMotifParam([0x32, channel, 0x04], [note])
        else:
            self.__sendMotifParam([0x37, channel, 0x09], [note])

    def send_note_shift(self, channel, shift):
        if channel < 4:
            self.__sendMotifParam([0x32, channel, 0x01], [64 + int(shift/12)])  # octave
            self.__sendMotifParam([0x32, channel, 0x02], [64 + (shift % 12 if shift >= 0 else -((-shift) % 12))])  # semitone
        else:
            self.__sendMotifParam([0x37, channel, 0x17], [64 + shift])

    def send_enabled(self, channel, enabled):
        if channel < 4:
            self.__sendMotifParam([0x32, channel, 0x00], [channel | (0x20 if enabled else 0x00)])
        else:
            self.__sendMotifParam([0x37, channel, 0x04], [8 if enabled else 0x7F])

    def __sendMidi(self, msg):
        self.socket_output.send(bytearray(msg))
        sleep(0.05)

    def __sendMotifParam(self, address, value):
        self.__sendMidi([0xF0, 0x43, 0x10, 0x7F, 0x03, *address, *value, 0xF7])


if __name__ == '__main__':
    midi = MidiSocket()
    midi.send_program_change(2, (63, 1, 1))
    midi.send_volume_change(2, 122)
