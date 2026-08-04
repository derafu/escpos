<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\Transport;

use Derafu\Escpos\Exception\NetworkDeliveryException;

/**
 * Delivers already-generated ESC/POS bytes to a network printer over TCP
 * (port 9100 is the de facto standard for raw ESC/POS printing).
 *
 * This is plain PHP streams, with no dependency on the underlying ESC/POS
 * library — it only moves a finished byte string to a socket, which has
 * nothing to do with how those bytes were produced.
 */
final class NetworkTransport
{
    /** De facto standard TCP port for raw ESC/POS printing. */
    public const int DEFAULT_PORT = 9100;

    /**
     * @throws NetworkDeliveryException If the connection fails, or not all
     * bytes could be written.
     */
    public function send(
        string $bytes,
        string $host,
        int $port = self::DEFAULT_PORT,
        int $timeoutSeconds = 5
    ): void {
        $errorNumber = 0;
        $errorMessage = '';

        // stream_socket_client() emits a warning on connection failure in
        // addition to returning false; $errorNumber/$errorMessage already
        // capture the same information, which the check right below turns
        // into a proper exception, so the warning is redundant noise.
        $socket = @stream_socket_client(
            sprintf('tcp://%s:%d', $host, $port),
            $errorNumber,
            $errorMessage,
            $timeoutSeconds
        );

        if ($socket === false) {
            throw new NetworkDeliveryException(sprintf(
                'Could not connect to %s:%d: %s (error %d).',
                $host,
                $port,
                $errorMessage,
                $errorNumber
            ));
        }

        try {
            $written = fwrite($socket, $bytes);

            if ($written === false || $written < strlen($bytes)) {
                throw new NetworkDeliveryException(sprintf(
                    'Could not write all %d bytes to %s:%d.',
                    strlen($bytes),
                    $host,
                    $port
                ));
            }
        } finally {
            fclose($socket);
        }
    }
}
