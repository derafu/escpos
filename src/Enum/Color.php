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
 * Print color, for the two-color thermal printers that support it.
 *
 * Values match the ESC/POS protocol, not an arbitrary numbering.
 */
enum Color: int
{
    case Color1 = 0;
    case Color2 = 1;
}
