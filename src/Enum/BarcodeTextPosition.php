<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\Enum;

/**
 * Position of the human-readable text printed alongside a barcode.
 *
 * Values match the ESC/POS protocol, not an arbitrary numbering.
 */
enum BarcodeTextPosition: int
{
    case None = 0;
    case Above = 1;
    case Below = 2;
}
