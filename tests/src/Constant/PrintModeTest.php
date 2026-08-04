<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos\Constant;

use Derafu\Escpos\Constant\PrintMode;
use Mike42\Escpos\Printer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrintMode::class)]
final class PrintModeTest extends TestCase
{
    #[Test]
    public function matchesMike42Constants(): void
    {
        $this->assertSame(Printer::MODE_FONT_A, PrintMode::FONT_A);
        $this->assertSame(Printer::MODE_FONT_B, PrintMode::FONT_B);
        $this->assertSame(Printer::MODE_EMPHASIZED, PrintMode::EMPHASIZED);
        $this->assertSame(Printer::MODE_DOUBLE_HEIGHT, PrintMode::DOUBLE_HEIGHT);
        $this->assertSame(Printer::MODE_DOUBLE_WIDTH, PrintMode::DOUBLE_WIDTH);
        $this->assertSame(Printer::MODE_UNDERLINE, PrintMode::UNDERLINE);
    }
}
