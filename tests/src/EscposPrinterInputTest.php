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

use Derafu\Array\Exception\TypeMismatchException;
use Derafu\Config\Options;
use Derafu\Escpos\Adapter\Mike42Printer;
use Derafu\Escpos\EscposPrinter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;
use TypeError;

#[CoversClass(EscposPrinter::class)]
#[UsesClass(Mike42Printer::class)]
final class EscposPrinterInputTest extends TestCase
{
    public function testPrintAndPrintlnPreserveScalarAndNullFormatting(): void
    {
        $printer = new EscposPrinter();
        $printer->print('%s|%d|%.2f|%s|%s|%s', 'item', 42, 2.5, true, false, null);
        $printer->println(':%d', 7);

        $this->assertSame("\x1b@item|42|2.50|1||:7\n", $printer->dump());
    }

    public function testStringableValuesUseTheirTextInStringAndNumericFormats(): void
    {
        $value = new class () implements Stringable {
            public function __toString(): string
            {
                return '42';
            }
        };
        $printer = new EscposPrinter();
        $printer->print('%s:%d:%.1f', $value, $value, $value);
        $printer->println(':%s', $value);

        $this->assertSame("\x1b@42:42:42.0:42\n", $printer->dump());
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function invalidFormattingValues(): iterable
    {
        foreach (['print', 'println'] as $method) {
            yield $method . ' array' => [$method, []];
            yield $method . ' object' => [$method, new stdClass()];
        }
    }

    #[DataProvider('invalidFormattingValues')]
    public function testFormattingRejectsUnsupportedValues(string $method, mixed $value): void
    {
        $printer = new EscposPrinter();
        $this->expectException(TypeError::class);
        $printer->$method('%s', $value);
    }

    public function testFormattingRejectsResources(): void
    {
        $stream = fopen('php://temp', 'w+');
        $this->assertIsResource($stream);
        $printer = new EscposPrinter();
        try {
            $this->expectException(TypeError::class);
            $printer->print('%s', $stream);
        } finally {
            fclose($stream);
        }
    }

    /** @return iterable<string, array{string}> */
    public static function pulseOptions(): iterable
    {
        yield 'pin' => ['pin'];
        yield 'on duration' => ['on_ms'];
        yield 'off duration' => ['off_ms'];
    }

    #[DataProvider('pulseOptions')]
    public function testReplacementOptionsCannotPassStringsAsPulseIntegers(string $key): void
    {
        $printer = new EscposPrinter();
        $pulse = ['pin' => 0, 'on_ms' => 120, 'off_ms' => 240];
        $pulse[$key] = '1';
        $printer->setOptions(new Options(['cut' => true, 'specialchars' => true, 'pulse' => $pulse]));

        $this->expectException(TypeMismatchException::class);
        $printer->end();
    }

    public function testCustomPulseTimingAndPinReachThePrinter(): void
    {
        $printer = new EscposPrinter(['pulse' => ['pin' => 1, 'on_ms' => 100, 'off_ms' => 200]]);
        $this->assertSame("\x1b@\n\n\x1dVA\x03\x1bp1\x32\x64", $printer->end());
    }

    public function testDisablingCutAlsoDisablesTheDrawerPulse(): void
    {
        $printer = new EscposPrinter(['cut' => false]);
        $printer->print('receipt');
        $this->assertSame("\x1b@receipt\n\n", $printer->end());
    }

    public function testSpecialCharacterReplacementRemainsOptional(): void
    {
        $printer = new EscposPrinter(['specialchars' => false]);
        $printer->println('áéíóúñÑ°');
        $this->assertSame("\x1b@aeiounN\n", $printer->dump());
    }
}
