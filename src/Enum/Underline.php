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
 * Underline thickness for the dedicated underline command (`setUnderline()`).
 *
 * This is a separate concept from the underline bit inside `PrintMode`: that
 * one only turns underline on/off, while this also selects single vs double
 * thickness. Values match the ESC/POS protocol, not an arbitrary numbering.
 */
enum Underline: int
{
    case None = 0;
    case Single = 1;
    case Double = 2;
}
