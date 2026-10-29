<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos;

use Derafu\Escpos\EscposPrinter;
use Derafu\Escpos\Exception\NetworkDeliveryException;
use Derafu\Escpos\Renderer\Pdf417Renderer;
use Derafu\Escpos\Transport\NetworkTransport;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Exercise the real YAML's shared helpers with generated graphics and TCP I/O.
 */
#[CoversNamespace('Derafu\Escpos')]
final class ServicesTest extends TestCase
{
    private function container(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new YamlFileLoader($container, new FileLocator(__DIR__ . '/../fixtures/config')))
            ->load('services.yaml');
        $container->compile();

        return $container;
    }

    private function transport(ContainerBuilder $container): NetworkTransport
    {
        $transport = $container->get('test.network_transport');
        $this->assertInstanceOf(NetworkTransport::class, $transport);

        return $transport;
    }

    public function testConfiguredRendererProducesAnImageUsableByIndependentPrintJobs(): void
    {
        $container = $this->container();
        $renderer = $container->get('test.pdf417_renderer');
        $this->assertInstanceOf(Pdf417Renderer::class, $renderer);
        $image = $renderer->render('LIBREDTE-TIMBRE-DATA');
        $this->assertGreaterThan(0, imagesx($image->getResource()));
        $this->assertGreaterThan(0, imagesy($image->getResource()));

        // Jobs own their buffers and options; only the renderer is shared.
        $first = new EscposPrinter(['cut' => false]);
        $second = new EscposPrinter(['cut' => false]);
        $before = $second->dump();
        $first->println('First receipt')->image($image);
        $firstOutput = $first->end();
        $this->assertStringContainsString('First receipt', $firstOutput);
        $this->assertStringContainsString("\x1d\x28\x4c", $firstOutput);
        $this->assertSame($before, $second->dump());

        $second->println('Second receipt');
        $this->assertStringContainsString('Second receipt', $second->end());
        $this->assertStringNotContainsString('First receipt', $second->dump());
        $this->assertSame($firstOutput, $first->dump());
    }

    public function testConfiguredTransportDeliversTheExactReceiptToALocalTcpServer(): void
    {
        $transport = $this->transport($this->container());
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $this->assertIsResource($server);
        try {
            $address = stream_socket_get_name($server, false);
            $this->assertIsString($address);
            $port = (int) substr($address, strrpos($address, ':') + 1);
            $printer = new EscposPrinter(['cut' => false]);
            $bytes = $printer->println('Configured transport')->end();

            $transport->send($bytes, '127.0.0.1', $port, 1);
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

    public function testTaggedResourcesTranslateTheConfiguredTransportsConnectionFailure(): void
    {
        $container = $this->container();
        $transport = $this->transport($container);
        $translator = $container->get('test.translator');
        $this->assertInstanceOf(Translator::class, $translator);

        // Reserve a TCP port without listening, so connections are refused
        // without allowing another process to claim that port.
        $reservation = stream_socket_server(
            'tcp://127.0.0.1:0',
            flags: STREAM_SERVER_BIND
        );
        $this->assertIsResource($reservation);
        try {
            $address = stream_socket_get_name($reservation, false);
            $this->assertIsString($address);
            $port = (int) substr($address, strrpos($address, ':') + 1);
            try {
                $transport->send('Receipt', '127.0.0.1', $port, 1);
                $this->fail('A connection to a non-listening port must fail.');
            } catch (NetworkDeliveryException $exception) {
                $this->assertStringStartsWith('Could not connect to 127.0.0.1:' . $port . ':', $exception->getMessage());
                $this->assertStringStartsWith('No se pudo conectar a 127.0.0.1:' . $port . ':', $exception->trans($translator));
            }
        } finally {
            fclose($reservation);
        }
    }
}
