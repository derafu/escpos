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

use Derafu\Escpos\Enum\Justification;
use Mike42\Escpos\Printer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Justification::class)]
final class JustificationTest extends TestCase
{
    #[Test]
    public function matchesMike42Constants(): void
    {
        $this->assertSame(Printer::JUSTIFY_LEFT, Justification::Left->value);
        $this->assertSame(Printer::JUSTIFY_CENTER, Justification::Center->value);
        $this->assertSame(Printer::JUSTIFY_RIGHT, Justification::Right->value);
    }
}
