<?php

declare(strict_types=1);

/**
 * Derafu: ESC/POS - Receipt printer library (Mike42 backend).
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos;

use Derafu\Escpos\Adapter\Mike42Printer;
use Derafu\Escpos\EscposPrinter;
use Mike42\Escpos\PrintConnectors\MemoryPrintConnector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * The `connector` option is either the configuration of a connector (an array
 * with its `type`) or an already-built connector instance.
 */
#[CoversClass(EscposPrinter::class)]
#[UsesClass(Mike42Printer::class)]
final class EscposPrinterConnectorOptionTest extends TestCase
{
    #[Test]
    public function acceptsAnAlreadyBuiltConnectorInstance(): void
    {
        $connector = new MemoryPrintConnector();

        $printer = new EscposPrinter(['connector' => $connector]);
        $printer->println('Hello');
        $printer->end();

        // What the printer sent went through the instance that was passed.
        $this->assertStringContainsString('Hello', $connector->getData());
    }

    #[Test]
    public function acceptsTheConfigurationOfAConnector(): void
    {
        $printer = new EscposPrinter(['connector' => ['type' => 'memory']]);
        $printer->println('Hello');

        $this->assertStringContainsString('Hello', $printer->end());
    }

    #[Test]
    public function usesTheMemoryConnectorByDefault(): void
    {
        $printer = new EscposPrinter();
        $printer->println('Hello');

        $this->assertStringContainsString('Hello', $printer->end());
    }
}
