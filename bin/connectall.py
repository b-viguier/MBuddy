#!/usr/bin/python2

import os
import subprocess
import re

# unconnect everything
os.system("aconnect -x")

interfaces = {}
current_client = None
current_client_id = None
output = subprocess.check_output("aconnect -i -l", shell=True)
for line in output.splitlines():
    match = re.search(r"client (\d+): '(.+)'", line)
    if match:
        interfaces[match.group(2).strip()] = current_client = {}
        current_client_id = match.group(1)
    else:
        match = re.search(r"\s+(\d+) '(.+)'", line)
        if match:
            current_client[match.group(2).strip()] = (current_client_id, match.group(1))


def aconnect(src=None, dst=None, src_id=None, dst_id=None):
    print("aconnect %s:%s %s:%s" % (src, src_id, dst, dst_id))
    os.system("aconnect %s:%s %s:%s" % (src, src_id, dst, dst_id))


def find_interface(client, name):
    try:
        return interfaces[client][name]
    except KeyError:
        return None


def connect_bidirectional(interface1, interface2):
    if interface1 and interface2:
        aconnect(src=interface1[0], src_id=interface1[1], dst=interface2[0], dst_id=interface2[1])
        aconnect(src=interface2[0], src_id=interface2[1], dst=interface1[0], dst_id=interface1[1])


motif = find_interface('YAMAHA MOTIF XS6', 'YAMAHA MOTIF XS6 MIDI 1')
impulse = find_interface('Impulse', 'Impulse MIDI 1')
iPad = find_interface('rtpmidi raspberrypi', 'iPad ebv')

connect_bidirectional(motif, impulse)
connect_bidirectional(motif, iPad)
connect_bidirectional(impulse, iPad)
