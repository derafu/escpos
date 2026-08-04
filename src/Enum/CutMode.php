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
 * How much the paper is cut after printing.
 *
 * Values match the ESC/POS protocol, not an arbitrary numbering.
 */
enum CutMode: int
{
    case Full = 65;
    case Partial = 66;
}
