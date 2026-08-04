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

use Derafu\Escpos\Enum\CutMode;
use Mike42\Escpos\Printer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CutMode::class)]
final class CutModeTest extends TestCase
{
    #[Test]
    public function matchesMike42Constants(): void
    {
        $this->assertSame(Printer::CUT_FULL, CutMode::Full->value);
        $this->assertSame(Printer::CUT_PARTIAL, CutMode::Partial->value);
    }
}
