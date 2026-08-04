#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

// -----------------------------------------------------------------------------
// Sends raw ESC/POS bytes to a network printer. Development/testing tool,
// not part of the package's public API.
//
// Usage:
//   bin/print.php <host>[:<port>] [<file>]
//
// If <port> is omitted, NetworkTransport::DEFAULT_PORT (9100) is used.
// If <file> is omitted, bytes are read from stdin, so this also works as
// the receiving end of a pipe:
//
//   bin/print.php 172.16.1.5:9100 ticket.escpos
//   cat ticket.escpos | bin/print.php 172.16.1.5
//   php examples/full-example.php | bin/print.php 172.16.1.5
// -----------------------------------------------------------------------------

require_once __DIR__ . '/../vendor/autoload.php';

use Derafu\Escpos\Exception\NetworkDeliveryException;
use Derafu\Escpos\Transport\NetworkTransport;

$destination = $argv[1] ?? null;
$file = $argv[2] ?? null;

if ($destination === null) {
    fwrite(STDERR, "Usage: bin/print.php <host>[:<port>] [<file>]\n");
    exit(1);
}

if (str_contains($destination, ':')) {
    [$host, $port] = explode(':', $destination, 2);
    $port = (int) $port;
} else {
    $host = $destination;
    $port = NetworkTransport::DEFAULT_PORT;
}

// file_get_contents() emits a warning on a missing/unreadable file in
// addition to returning false; the check right below already turns that
// into a clear error message, so the warning is redundant noise.
$bytes = $file !== null
    ? @file_get_contents($file)
    : file_get_contents('php://stdin');

if ($bytes === false) {
    fwrite(STDERR, sprintf("Could not read %s.\n", $file ?? 'stdin'));
    exit(1);
}

try {
    (new NetworkTransport())->send($bytes, $host, $port);
} catch (NetworkDeliveryException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

printf("Sent %d bytes to %s:%d.\n", strlen($bytes), $host, $port);
