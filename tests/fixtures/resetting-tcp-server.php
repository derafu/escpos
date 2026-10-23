<?php

declare(strict_types=1);

/**
 * TCP receiver that resets a connection after receiving its first byte.
 *
 * The bound address is printed before accepting the connection, allowing the
 * test to start sending only when the receiver is ready.
 */

$server = stream_socket_server('tcp://127.0.0.1:0');
if ($server === false) {
    throw new RuntimeException('Could not create the TCP receiver.');
}

try {
    $address = stream_socket_get_name($server, false);
    if ($address === false) {
        throw new RuntimeException('Could not determine the TCP receiver address.');
    }
    fwrite(STDOUT, $address . "\n");
    fflush(STDOUT);

    $peer = stream_socket_accept($server, 5);
    if ($peer === false) {
        throw new RuntimeException('No client connected to the TCP receiver.');
    }

    try {
        $socket = socket_import_stream($peer);
        if ($socket === false || !socket_set_option(
            $socket,
            SOL_SOCKET,
            SO_LINGER,
            ['l_onoff' => 1, 'l_linger' => 0]
        )) {
            throw new RuntimeException('Could not configure a TCP connection reset.');
        }

        stream_set_timeout($peer, 5);
        $byte = fread($peer, 1);
        if ($byte === false || $byte === '') {
            throw new RuntimeException('The client did not send any data.');
        }
    } finally {
        fclose($peer);
    }
} finally {
    fclose($server);
}
