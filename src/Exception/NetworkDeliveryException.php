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

use RuntimeException;

/**
 * Thrown when ESC/POS bytes could not be delivered to a network printer.
 */
final class NetworkDeliveryException extends RuntimeException implements EscposExceptionInterface
{
}
