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

use Derafu\Escpos\Adapter\Mike42Printer;
use Derafu\Escpos\Constant\PrintMode;
use Derafu\Escpos\Enum\BarcodeType;
use Derafu\Escpos\Enum\Justification;
use Derafu\Escpos\EscposPrinter;
use Derafu\Escpos\ValueObject\RasterImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Unlike `EscposPrinterCharacterizationTest`, which pins each method in
 * isolation (a fresh `EscposPrinter` per call), this pins a single,
 * "ugly" but realistic chain of calls on *one* instance — closer to how a
 * real consumer (a receipt renderer) actually drives the printer: mixing
 * print modes, justifications, a barcode, a PDF417 code and an image
 * before finishing the job. It exists to catch anything that only shows up
 * when commands interact on a shared buffer, which the isolated tests
 * cannot see.
 *
 * The expected output lives in a fixture file instead of an inline
 * literal because it is long and mixes several composite commands — the
 * same reasoning already used for `barcode`/`pdf417`/`image` in the
 * isolated tests, just at a larger scale.
 */
#[CoversClass(EscposPrinter::class)]
#[UsesClass(Mike42Printer::class)]
#[UsesClass(RasterImage::class)]
final class EscposPrinterSequenceTest extends TestCase
{
    #[Test]
    public function printsARealisticMixedSequence(): void
    {
        $printer = new EscposPrinter();

        $printer
            ->selectPrintMode(PrintMode::FONT_A | PrintMode::EMPHASIZED)
            ->setJustification(Justification::Center)
            ->println('ACME S.A.')
            ->selectPrintMode(PrintMode::FONT_B)
            ->setJustification(Justification::Left)
            ->println('R.U.T.: %s', '76192083-9')
            ->println('Folio N°: %d', 123)
            ->feed(1)
            ->setJustification(Justification::Right)
            ->println('Total: $%s', '19.990')
            ->setJustification(Justification::Center)
            ->feed(1)
            ->barcode('ABC123', BarcodeType::Code39, 48)
            ->feed(1)
            ->pdf417('LIBREDTE-TIMBRE-DATA');

        $gd = imagecreatetruecolor(8, 8);
        imagefill($gd, 0, 0, imagecolorallocate($gd, 255, 255, 255));
        imagesetpixel($gd, 0, 0, imagecolorallocate($gd, 0, 0, 0));
        imagesetpixel($gd, 7, 7, imagecolorallocate($gd, 0, 0, 0));
        $printer->image(new RasterImage($gd));

        $printer->feed(2);

        $expected = file_get_contents(
            __DIR__ . '/../fixtures/escpos-printer-sequence.escpos'
        );

        $this->assertSame($expected, $printer->end());
    }
}
