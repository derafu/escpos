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
 * Pins the exact ESC/POS bytes that `EscposPrinter` produces today, against
 * Mike42 v5, for every method used by its real consumer (LibreDTE). This is
 * a characterization test: it does not assert what the output *should* be,
 * only what it *currently is* (including quirks, like the leading `ESC @`
 * below), so an internal refactor can be checked against this baseline
 * instead of relying on manual testing in a consumer project.
 *
 * This exercises `EscposPrinter` together with its default backend
 * (`Mike42Printer`) end-to-end, on purpose: the whole point is pinning the
 * real bytes that reach the printer, not just `EscposPrinter`'s own code in
 * isolation. Hence `UsesClass` for its collaborators, not `CoversClass`.
 */
#[CoversClass(EscposPrinter::class)]
#[UsesClass(Mike42Printer::class)]
#[UsesClass(RasterImage::class)]
final class EscposPrinterCharacterizationTest extends TestCase
{
    /**
     * `Mike42\Escpos\Printer::__construct()` calls `initialize()`, which
     * writes `ESC @` before anything else. This happens lazily, on the
     * first command sent through the wrapper (via the private
     * `getPrinter()`), not on `new EscposPrinter()` itself.
     */
    #[Test]
    public function initializeIsWrittenBeforeTheFirstCommand(): void
    {
        $printer = new EscposPrinter();
        $printer->print('');

        $this->assertSame("\x1b@", $printer->dump());
    }

    #[Test]
    public function printsText(): void
    {
        $printer = new EscposPrinter();
        $printer->print('Hello, world!');

        $this->assertSame("\x1b@Hello, world!", $printer->dump());
    }

    #[Test]
    public function printsLineWithSprintfArgumentsAndTrailingNewline(): void
    {
        $printer = new EscposPrinter();
        $printer->println('R.U.T.: %s', '76192083-9');

        $this->assertSame("\x1b@R.U.T.: 76192083-9\n", $printer->dump());
    }

    #[Test]
    public function selectsPrintMode(): void
    {
        $printer = new EscposPrinter();
        $printer->selectPrintMode(PrintMode::FONT_A | PrintMode::EMPHASIZED);
        $this->assertSame("\x1b@\x1b!" . chr(8), $printer->dump());

        $printer = new EscposPrinter();
        $printer->selectPrintMode(PrintMode::FONT_B | PrintMode::EMPHASIZED);
        $this->assertSame("\x1b@\x1b!" . chr(9), $printer->dump());
    }

    #[Test]
    public function setsJustification(): void
    {
        $printer = new EscposPrinter();
        $printer->setJustification(Justification::Left);
        $this->assertSame("\x1b@\x1ba" . chr(0), $printer->dump());

        $printer = new EscposPrinter();
        $printer->setJustification(Justification::Center);
        $this->assertSame("\x1b@\x1ba" . chr(1), $printer->dump());

        $printer = new EscposPrinter();
        $printer->setJustification(Justification::Right);
        $this->assertSame("\x1b@\x1ba" . chr(2), $printer->dump());
    }

    #[Test]
    public function feedsLines(): void
    {
        // 1 line uses a plain line feed.
        $printer = new EscposPrinter();
        $printer->feed(1);
        $this->assertSame("\x1b@\n", $printer->dump());

        // More than 1 line uses the dedicated ESC/POS command.
        $printer = new EscposPrinter();
        $printer->feed(3);
        $this->assertSame("\x1b@\x1bd" . chr(3), $printer->dump());
    }

    #[Test]
    public function printsBarcode(): void
    {
        $printer = new EscposPrinter();
        $printer->barcode('ABC123', BarcodeType::Code39, 48);

        $this->assertSame(
            hex2bin('1b401d68301d6b4506414243313233'),
            $printer->dump()
        );
    }

    /**
     * Composite command, pinned from the current behavior rather than
     * hand-derived, since `pdf417Code()` builds a multi-field command
     * internally. If this baseline changes after a refactor, it must be
     * investigated, not silently updated.
     */
    #[Test]
    public function printsPdf417(): void
    {
        $printer = new EscposPrinter();
        $printer->pdf417('LIBREDTE-TIMBRE-DATA');

        $this->assertSame(
            hex2bin(
                '1b401d286b03003046001d286b03003041001d286b03003043021d286b0300'
                . '3044021d286b0400304531011d286b17003050304c494252454454452d54'
                . '494d4252452d444154411d286b0300305130'
            ),
            $printer->dump()
        );
    }

    /**
     * Composite command, pinned from the current behavior. The image is a
     * deterministic 8x8 bitmap built at test time, so this still fails
     * loudly if `image()` changes how it converts a GD resource into
     * ESC/POS raster data.
     */
    #[Test]
    public function printsImage(): void
    {
        $gd = imagecreatetruecolor(8, 8);
        imagefill($gd, 0, 0, imagecolorallocate($gd, 255, 255, 255));
        imagesetpixel($gd, 0, 0, imagecolorallocate($gd, 0, 0, 0));
        imagesetpixel($gd, 7, 7, imagecolorallocate($gd, 0, 0, 0));

        $printer = new EscposPrinter();
        $printer->image(new RasterImage($gd));

        $this->assertSame(
            hex2bin(
                '1b401d284c12003070300101310800080080000000000000011d284c0200'
                . '3032'
            ),
            $printer->dump()
        );
    }

    #[Test]
    public function endsWithTrailingLinesCutAndPulse(): void
    {
        $printer = new EscposPrinter();
        $printer->print('x');

        $this->assertSame(
            "\x1b@x\n\n" . "\x1dVA" . chr(3) . "\x1bp0" . chr(60) . chr(120),
            $printer->end()
        );
    }
}
