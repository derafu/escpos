<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\ValueObject;

use Derafu\Escpos\Exception\InvalidImageDataException;
use GdImage;

/**
 * An already-rendered raster image, ready to be sent to `image()`.
 *
 * This exists so that printing an image never requires the caller to know
 * about (or construct) any class from the underlying ESC/POS library. The
 * caller is responsible for producing the pixels (from a barcode renderer,
 * a logo file, etc.); this package only knows how to print them.
 */
final class RasterImage
{
    public function __construct(private readonly GdImage $resource)
    {
    }

    /**
     * @throws InvalidImageDataException If $data cannot be decoded as an
     * image.
     */
    public static function fromPngData(string $data): self
    {
        // imagecreatefromstring() emits a warning on invalid data instead
        // of just returning false; the check right below already turns
        // that into a proper exception, so the warning is redundant noise.
        $resource = @imagecreatefromstring($data);

        if (!$resource instanceof GdImage) {
            throw new InvalidImageDataException(
                'Could not decode the given PNG data.'
            );
        }

        return new self($resource);
    }

    public function getResource(): GdImage
    {
        return $this->resource;
    }
}
