<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos\Renderer;

use Derafu\Escpos\Adapter\Mike42Printer;
use Derafu\Escpos\EscposPrinter;
use Derafu\Escpos\Renderer\Pdf417Renderer;
use Derafu\Escpos\ValueObject\RasterImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Does not pin the exact PNG bytes tc-lib-barcode produces — GD's PNG
 * encoder can compress identical pixels differently across versions, which
 * would make that a flaky test, not a useful one. Instead this checks
 * properties that must hold regardless of encoder details: rendering is
 * deterministic for the same content, different content renders
 * differently (it is not just returning a static image), and the result
 * is actually usable by `EscposPrinter::image()`.
 */
#[CoversClass(Pdf417Renderer::class)]
#[UsesClass(RasterImage::class)]
#[UsesClass(EscposPrinter::class)]
#[UsesClass(Mike42Printer::class)]
final class Pdf417RendererTest extends TestCase
{
    #[Test]
    public function rendersTheSameContentDeterministically(): void
    {
        $renderer = new Pdf417Renderer();

        $first = $renderer->render('LIBREDTE-TIMBRE-DATA');
        $second = $renderer->render('LIBREDTE-TIMBRE-DATA');

        $this->assertSame(
            $this->decodedPixelsOf($first),
            $this->decodedPixelsOf($second)
        );
    }

    #[Test]
    public function rendersDifferentContentDifferently(): void
    {
        $renderer = new Pdf417Renderer();

        $a = $renderer->render('AAAAAAAAAA');
        $b = $renderer->render('BBBBBBBBBB');

        $this->assertNotSame(
            $this->decodedPixelsOf($a),
            $this->decodedPixelsOf($b)
        );
    }

    #[Test]
    public function producesAnImageThatEscposPrinterCanActuallyPrint(): void
    {
        $renderer = new Pdf417Renderer();
        $image = $renderer->render('LIBREDTE-TIMBRE-DATA');

        $printer = new EscposPrinter();
        $printer->image($image);

        // GS ( L is the ESC/POS graphics command header; its presence
        // confirms EscposPrinter treated this as a real image, not that it
        // silently no-op'd or errored out.
        $this->assertStringContainsString("\x1d\x28\x4c", $printer->dump());
    }

    private function decodedPixelsOf(RasterImage $image): string
    {
        ob_start();
        imagepng($image->getResource());

        return ob_get_clean();
    }
}
