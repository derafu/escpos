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
 * Whether a printed PDF417 code includes start/end bars or only start bars.
 *
 * Values match the ESC/POS protocol, not an arbitrary numbering.
 */
enum Pdf417Option: int
{
    case Standard = 0;
    case Truncated = 1;
}
