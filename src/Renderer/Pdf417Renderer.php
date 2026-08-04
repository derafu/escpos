<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\Renderer;

use Com\Tecnick\Barcode\Barcode;
use Derafu\Escpos\ValueObject\RasterImage;

/**
 * The only class in this package allowed to `use Com\Tecnick\Barcode\*`.
 *
 * Renders a PDF417 code as a raster image, for printers that cannot print
 * it with the native ESC/POS command (`EscposPrinter::pdf417()`). This is a
 * rendering concern, not a printing one — it produces a `RasterImage` that
 * the caller then hands to `EscposPrinter::image()`; it does not print
 * anything itself, and `EscposPrinter` has no dependency on it.
 */
final class Pdf417Renderer
{
    public function render(string $content): RasterImage
    {
        $barcode = (new Barcode())->getBarcodeObj('PDF417,,5', $content);

        return RasterImage::fromPngData($barcode->getPngData(false));
    }
}
