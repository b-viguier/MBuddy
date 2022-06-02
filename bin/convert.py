import string
import sys
import time
import Bulk

import rtmidi

if __name__ != "__main__":
    print("Invalid script call")
    exit(1)

if len(sys.argv) != 2:
    print("Invalid nb of parameters")
    exit(2)

filename = sys.argv[1]
print("Opening '%s'" % filename)
with open(filename) as file:
    dict = eval(file.read())


def msg2str(msg):
    return [hex(byte) for byte in msg]


def select_port(midi, name):
    available_ports = midi.get_ports()
    for port, port_name in enumerate(available_ports):
        print("[%d][%s]" % (port, port_name))
    port = available_ports.index(name)
    midi.open_port(port)
    return midi


def get_param(midiout, midiin, address):
    msg = [0xF0, 0x43, 0x30, 0x7F, 0x03, *address, 0xF7]
    midiout.send_message(msg)
    msg = wait_response(midiin)
    if msg and len(msg) == 10 and msg[0:8] == [0xF0, 0x43, 0x10, 0x7F, 0x03, *address] and msg[9] == 0xF7:
        return msg[8]

    return None


def set_param(midiout, address, value):
    msg = [0xF0, 0x43, 0x10, 0x7F, 0x03, *address, value, 0xF7]
    midiout.send_message(msg)
    time.sleep(0.5)


def wait_response(midiin):
    for attempt in range(1, 100):
        time.sleep(0.05)
        msg = midiin.get_message()
        if msg is not None:
            return msg[0]
    return None


def wait_bulk(midiin, nb):
    bulk = []
    for attempt in range(1, 100):
        time.sleep(0.05)
        msg_time = midiin.get_message()
        if msg_time is not None:
            bulk.append(msg_time[0])
            if len(bulk) == nb:
                return bulk
    return None


def get_master_title(midiout, midiin):
    title = ""
    for i in range(0, 19):
        char = get_param(midiout, midiin, [0x33, 0x00, i])
        title = title + chr(char)
    return title


def get_dump(midiout, midiin, address):
    midiout.send_message([0xF0, 0x43, 0x20, 0x7F, 0x03, *address, 0xF7])
    msg = wait_response(midiin)
    while msg:
        print(msg2str(msg))
        msg = wait_response(midiin)


midiout = select_port(rtmidi.MidiOut(), "WIDI Jack:WIDI Jack Bluetooth 129:0")
midiin = select_port(rtmidi.MidiIn(), "WIDI Jack:WIDI Jack Bluetooth 129:0")
midiin.ignore_types(False, True, True)

# msg = wait_response(midiin)
# if msg is not None:
#     print("Error, queue not empty")
#     print(msg2str(msg))
#     exit(3)


# print("Get identity")
# midiout.send_message([0xF0, 0x7E, 0x00, 0x06, 0x01, 0xF7])
# print(msg2str(wait_response(midiin)))
#
# print("Get current mode")
# mode = get_param(midiout, midiin, [0x0A, 0x00, 0x01])
# print(mode)
#
# if mode != 4:
#     print("Set current mode")
#     set_param(midiout, [0x0A, 0x00, 0x01], 4)
#
#     print("Get current mode")
#     mode = get_param(midiout, midiin, [0x0A, 0x00, 0x01])
#     print(mode)
#
#     if mode != 4:
#         exit(5)
#
# print("get title")
# title = get_master_title(midiout, midiin)
# print(title)
#
# get_dump(midiout, midiin, [0x0E, 0x70, 0x01])


print("Send Dump Request")
midiout.send_message(Bulk.MasterDump.get_dump_request(-1))
print("Wait Response")
bulk = wait_bulk(midiin, Bulk.MasterDump.get_nb_blocks())
if bulk is None:
    print("Cannot receive Bulk")
    exit(5)

print("Read Response")
dump = Bulk.MasterDump(-1, bulk)
dump.print()
