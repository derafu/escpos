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
 * Error correction level for a printed QR code.
 *
 * Values match the ESC/POS protocol, not an arbitrary numbering.
 */
enum QrErrorCorrectionLevel: int
{
    case L = 0;
    case M = 1;
    case Q = 2;
    case H = 3;
}
