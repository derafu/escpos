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

use Derafu\Translation\Exception\Core\TranslatableRuntimeException;

/**
 * Thrown when reading the printed output is requested from a connector that
 * does not buffer data in memory (e.g. a real network or file connector).
 */
final class UnbufferedConnectorException extends TranslatableRuntimeException implements EscposExceptionInterface
{
}
