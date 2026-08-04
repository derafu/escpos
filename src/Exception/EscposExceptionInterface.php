<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\Exception;

use Throwable;

/**
 * Marker interface implemented by every exception thrown by this package.
 *
 * Lets a consumer catch anything `derafu/escpos` throws without needing to
 * know (or import) the specific exception class.
 */
interface EscposExceptionInterface extends Throwable
{
}
