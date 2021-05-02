# MBuddy

A Midi companion to manage my MIDI devices.

## Features / How To
* Events from `Impulse` are forwarded to `Pa50`
* MBuddy listen `Pa50` channel 16 to know which performance is currently loaded.
    * If bank `MSB`/`LSB` are not `0`/`0`, performance is considered _unmanaged_
    * else, the `Program` id is used to identify the current `SongId`.
    * The `Program` id `0` is reserved for the default `SongId`.
    * MBuddy try to load corresponding `Impulse` patch, else create a new one from default patch.
* MBuddy listen `Impulse` sysex dumps
    * If its name matches MBuddy pattern, it saves the corresponding patch on disk, associated with the current `SongId`.
    * If its name doesn't match the pattern, or if it doesn't match current `Pa50` `SongId`, nothing is done.
* It's possible to modify the `SongId` of the current `Pa50` performance by maintaining `Impulse` _record_ button pressed
and using `Impulse` _previous_/_next_ buttons.
    * If the performance is _unmanaged_, a default initialization value will be used.
    * If the performance is already _managed_, the value will be respectively decremented or incremented.
        