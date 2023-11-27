# NewsSettings

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL"
in this document are to be interpreted as described in
[RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**

* [Requirements](#requirements)
* [Installation](#installation)
  * [Composer](#composer)
* [Configuration](#configuration)
* [Specifications](#specifications)
* [Other information](#other-information)
  * [Correlations](#correlations)
  * [Bugs](#bugs)
  * [License](#license)

## Requirements

* PHP: [![Minimum PHP Version](https://img.shields.io/badge/Minimum_PHP-7.4.x-blue.svg)](https://php.net/) [![Maximum PHP Version](https://img.shields.io/badge/Maximum_PHP-8.0.x-blue.svg)](https://php.net/)
* ILIAS: [![Minimum ILIAS Version](https://img.shields.io/badge/Minimum_ILIAS-8.0-orange.svg)](https://ilias.de/) [![Maximum ILIAS Version](https://img.shields.io/badge/Maximum_ILIAS-8.999-orange.svg)](https://ilias.de/)

## Installation

This plugin MUST be installed as a EventHook Plugin.

	<ILIAS>/Customizing/global/plugins/Services/EventHandling/EventHook/NewsSettings

Correct file and folder permissions MUST be
ensured by the responsible system administrator.

### Composer

After the plugin files have been installed as described above,
please install the [`composer`](https://getcomposer.org/) dependencies:

```bash
cd Customizing/global/plugins/Services/EventHandling/EventHook/NewsSettings
composer install --no-dev
```

Developers MUST omit the `--no-dev` argument.

## Configuration

None

## Specifications

An ILIAS plugin that applies defaults to timeline service of new objects and provides
a user interface for timeline service migrations.

## Other Information

This plugin is a fork of the exisiting NewsSettings plugin form Databay created by Michael Jansen. You can find the original here: ['NewsSettings'](https://github.com/DatabayAG/NewsSettings/tree/master)

ILIAS 9: Own Plugin slot, and complete seperation form the Original.

### Correlations

None

### Bugs

None

### License

See [LICENSE](./LICENSE) file in this repository.
