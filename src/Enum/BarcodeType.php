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
 * Barcode symbology for the `barcode()` command.
 *
 * Values match the ESC/POS protocol (`GS k m`), not an arbitrary numbering.
 */
enum BarcodeType: int
{
    case Upca = 65;
    case Upce = 66;
    case Jan13 = 67;
    case Jan8 = 68;
    case Code39 = 69;
    case Itf = 70;
    case Codabar = 71;
    case Code93 = 72;
    case Code128 = 73;
}
