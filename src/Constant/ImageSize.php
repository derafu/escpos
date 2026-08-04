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
 * Image size flags for the `image()` command.
 *
 * These are independent bits meant to be combined with `|`, so they are
 * plain int constants rather than an enum. Values match the ESC/POS
 * protocol, not an arbitrary numbering.
 */
final class ImageSize
{
    public const int STANDARD = 0;

    public const int DOUBLE_WIDTH = 1;

    public const int DOUBLE_HEIGHT = 2;

    private function __construct()
    {
    }
}
