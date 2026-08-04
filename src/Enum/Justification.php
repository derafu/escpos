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
 * Text justification for the following writings on the printer.
 *
 * Values match the ESC/POS protocol (`ESC a n`), not an arbitrary numbering.
 */
enum Justification: int
{
    case Left = 0;
    case Center = 1;
    case Right = 2;
}
