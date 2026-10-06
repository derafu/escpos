<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\Exception;

use Derafu\Translation\Exception\Logic\TranslatableInvalidArgumentException;

/**
 * Thrown when raster image data (e.g. PNG bytes) cannot be decoded.
 */
final class InvalidImageDataException extends TranslatableInvalidArgumentException implements EscposExceptionInterface
{
}
