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
 * QR code model printed by a `qrCode()` command.
 *
 * Values match the ESC/POS protocol, not an arbitrary numbering.
 */
enum QrModel: int
{
    case Model1 = 1;
    case Model2 = 2;
    case Micro = 3;
}
