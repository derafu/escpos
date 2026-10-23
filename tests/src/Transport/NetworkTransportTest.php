<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos\Transport;

use Derafu\Escpos\Exception\NetworkDeliveryException;
use Derafu\Escpos\Transport\NetworkTransport;
use Derafu\Translation\Contract\TranslatableInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NetworkTransport::class)]
#[UsesClass(NetworkDeliveryException::class)]
final class NetworkTransportTest extends TestCase
{
    #[RequiresPhpExtension('sockets')]
    public function testAConnectionResetDuringDeliveryThrowsATranslatableException(): void
    {
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/../../fixtures/resetting-tcp-server.php'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        $this->assertIsResource($process);

        try {
            fclose($pipes[0]);
            stream_set_timeout($pipes[1], 5);
            stream_set_timeout($pipes[2], 5);
            $address = fgets($pipes[1]);
            $this->assertIsString($address);
            $this->assertMatchesRegularExpression('/^127\.0\.0\.1:[0-9]+\n$/', $address);
            $port = (int) substr(trim($address), strlen('127.0.0.1:'));

            // Exceed the socket buffer so resetting after the first byte
            // interrupts an ongoing delivery rather than a completed write.
            $bytes = str_repeat('x', 16 * 1024 * 1024);
            try {
                (new NetworkTransport())->send($bytes, '127.0.0.1', $port, 1);
                $this->fail('An interrupted delivery must not report success.');
            } catch (NetworkDeliveryException $e) {
                $this->assertInstanceOf(TranslatableInterface::class, $e);
                $this->assertSame(
                    'Could not write all ' . strlen($bytes) . ' bytes to 127.0.0.1:' . $port . '.',
                    $e->getMessage()
                );
            }

            $this->assertSame('', stream_get_contents($pipes[1]));
            $this->assertSame('', stream_get_contents($pipes[2]));
            fclose($pipes[1]);
            fclose($pipes[2]);
            $status = proc_close($process);
            $this->assertSame(0, $status);
        } finally {
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }
        }
    }

    public function testDeliversTheExactBinaryReceiptToARealTcpSocket(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $this->assertIsResource($server);
        try {
            $address = stream_socket_get_name($server, false);
            $this->assertIsString($address);
            $port = (int) substr($address, strrpos($address, ':') + 1);
            $bytes = "\x1b@Receipt\n\x00\xff\x1dVA\x03";

            (new NetworkTransport())->send($bytes, '127.0.0.1', $port, 1);
            $connection = stream_socket_accept($server, 1);
            $this->assertIsResource($connection);
            try {
                stream_set_timeout($connection, 1);
                $this->assertSame($bytes, stream_get_contents($connection));
            } finally {
                fclose($connection);
            }
        } finally {
            fclose($server);
        }
    }
}
