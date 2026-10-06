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

use Derafu\Translation\Exception\Logic\TranslatableInvalidArgumentException;

/**
 * Thrown when the configured printer capability profile does not exist.
 */
final class ProfileNotFoundException extends TranslatableInvalidArgumentException implements EscposExceptionInterface
{
}
