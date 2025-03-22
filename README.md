# MBuddy

A Midi companion to manage my MIDI devices.

## Installation
```shell
composer install
```

## Development
To run from a compatible Docker container:
```shell
make up
make bash
```
Then all commands below are available
```shell
composer install-tools
```

### Running app locally
```shell
make serve
```
http://localhost:8383/MBuddy

### Code style
```shell
composer cs-fix
```

### Static analysis
```shell
composer phpstan
```

### Javascript compatibility

Safari 9 only supports ES6.
A Node container is available with Babel to help transpiling big files.
```shell
make bash-node
```
Then from the container:
```shell
babel <input-file> > <output-file>
```
