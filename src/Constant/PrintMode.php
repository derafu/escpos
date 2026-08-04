<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\Constant;

/**
 * Print mode flags for the `selectPrintMode()` command.
 *
 * These are independent bits meant to be combined with `|` (e.g.
 * `PrintMode::FONT_B | PrintMode::EMPHASIZED`), so they are plain int
 * constants rather than an enum. Values match the ESC/POS protocol
 * (`ESC ! n`), not an arbitrary numbering.
 */
final class PrintMode
{
    public const int FONT_A = 0;

    public const int FONT_B = 1;

    public const int EMPHASIZED = 8;

    public const int DOUBLE_HEIGHT = 16;

    public const int DOUBLE_WIDTH = 32;

    public const int UNDERLINE = 128;

    private function __construct()
    {
    }
}
