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

use Derafu\Escpos\Enum\Underline;
use Mike42\Escpos\Printer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Underline::class)]
final class UnderlineTest extends TestCase
{
    #[Test]
    public function matchesMike42Constants(): void
    {
        $this->assertSame(Printer::UNDERLINE_NONE, Underline::None->value);
        $this->assertSame(Printer::UNDERLINE_SINGLE, Underline::Single->value);
        $this->assertSame(Printer::UNDERLINE_DOUBLE, Underline::Double->value);
    }
}
