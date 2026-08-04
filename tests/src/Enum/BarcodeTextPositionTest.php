<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos\Enum;

use Derafu\Escpos\Enum\BarcodeTextPosition;
use Mike42\Escpos\Printer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BarcodeTextPosition::class)]
final class BarcodeTextPositionTest extends TestCase
{
    #[Test]
    public function matchesMike42Constants(): void
    {
        $this->assertSame(Printer::BARCODE_TEXT_NONE, BarcodeTextPosition::None->value);
        $this->assertSame(Printer::BARCODE_TEXT_ABOVE, BarcodeTextPosition::Above->value);
        $this->assertSame(Printer::BARCODE_TEXT_BELOW, BarcodeTextPosition::Below->value);
    }
}
