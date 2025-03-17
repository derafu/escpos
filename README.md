# Derafu: ESCPOS - PHP Library for ESC/POS Printers

![GitHub last commit](https://img.shields.io/github/last-commit/derafu/escpos/main)
![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/derafu/escpos)
![GitHub Issues](https://img.shields.io/github/issues-raw/derafu/escpos)
![Total Downloads](https://poser.pugx.org/derafu/escpos/downloads)
![Monthly Downloads](https://poser.pugx.org/derafu/escpos/d/monthly)

A PHP library for generating ESC/POS commands to communicate with thermal receipt printers. This library is a wrapper around [Mike42/escpos-php](https://github.com/mike42/escpos-php) that provides a more fluent interface and additional features.

## Features

- Simplified API for common receipt printing tasks.
- Support for multiple connector types.
- Text formatting (alignment, font styles, etc.).
- 1D and 2D barcode generation (including PDF417).
- Special character handling and replacement.
- Receipt cutting and cash drawer opening.
- Chainable methods for cleaner code.

## Installation

You can install the package via composer:

```bash
composer require derafu/escpos
```

## Usage

### Basic Example

```php
<?php

use Derafu\Escpos\EscposPrinter;
use Mike42\Escpos\Printer;

// Initialize the printer.
$printer = new EscposPrinter([
    'connector' => ['type' => 'dummy'],
    'profile' => 'default',
]);

// Print some text.
$printer->println('Hello World!')
    ->setJustification(Printer::JUSTIFY_CENTER)
    ->println('Centered Text')
    ->println('Multiple lines')
    ->feed(2); // Add some empty lines.

// Get the ESC/POS data.
$escposData = $printer->end();

// Output the data (could be sent to printer).
echo $escposData;
```

### Full Example

The example show how to use all the features.

Run the example with:

```shell
php examples/full-example.php | nc 172.16.1.5 9100
```

**Note**: You need `netcat` installed in your system, and `172.16.1.5` `9100` are the IP and port of your thermal printer.

## Configuration Options

The `EscposPrinter` class accepts the following configuration options:

| Option       | Type   | Default                                         | Description                               |
|--------------|--------|-------------------------------------------------|-------------------------------------------|
| connector    | array  | `['type' => 'dummy']`                           | Printer connector configuration           |
| profile      | string | `'default'`                                     | Printer capability profile                |
| cut          | bool   | `true`                                          | Automatically cut paper at the end        |
| specialchars | bool   | `true`                                          | Replace special characters (like accents) |
| pulse        | array  | `['pin' => 0, 'on_ms' => 120, 'off_ms' => 240]` | Cash drawer opening configuration         |

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
