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
 * Character font selected via the dedicated font command (`setFont()`).
 *
 * This is a separate concept from the font bit inside `PrintMode`: that one
 * only distinguishes A/B within the print-mode byte, while this selects
 * among all fonts the printer defines (including C, on printers that have
 * it). Values match the ESC/POS protocol, not an arbitrary numbering.
 */
enum Font: int
{
    case A = 0;
    case B = 1;
    case C = 2;
}
