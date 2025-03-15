# MBuddy

A Midi companion to manage my MIDI devices.

## Installation
```
composer install
```

## Development
To run from a compatible Docker container:
```
make up
make bash
```
Then all commands below are available
```
composer install-tools
```

### Running app locally
```shell
make serve
```
http://localhost:8383/MBuddy

### Code style
```
composer cs-fix
```

### Static analysis
```
composer phpstan
```
