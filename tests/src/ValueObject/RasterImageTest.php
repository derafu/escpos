<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos\ValueObject;

use Derafu\Escpos\Exception\InvalidImageDataException;
use Derafu\Escpos\ValueObject\RasterImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RasterImage::class)]
final class RasterImageTest extends TestCase
{
    #[Test]
    public function decodesValidPngDataIntoAGdImageOfTheSameDimensions(): void
    {
        $original = imagecreatetruecolor(5, 3);

        ob_start();
        imagepng($original);
        $pngData = ob_get_clean();

        $image = RasterImage::fromPngData($pngData);

        $this->assertSame(5, imagesx($image->getResource()));
        $this->assertSame(3, imagesy($image->getResource()));
    }

    #[Test]
    public function rejectsDataThatCannotBeDecodedAsAnImage(): void
    {
        $this->expectException(InvalidImageDataException::class);

        RasterImage::fromPngData('this is not image data');
    }
}
