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

use Derafu\Escpos\Enum\QrErrorCorrectionLevel;
use Mike42\Escpos\Printer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(QrErrorCorrectionLevel::class)]
final class QrErrorCorrectionLevelTest extends TestCase
{
    #[Test]
    public function matchesMike42Constants(): void
    {
        $this->assertSame(Printer::QR_ECLEVEL_L, QrErrorCorrectionLevel::L->value);
        $this->assertSame(Printer::QR_ECLEVEL_M, QrErrorCorrectionLevel::M->value);
        $this->assertSame(Printer::QR_ECLEVEL_Q, QrErrorCorrectionLevel::Q->value);
        $this->assertSame(Printer::QR_ECLEVEL_H, QrErrorCorrectionLevel::H->value);
    }
}
