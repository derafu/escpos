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

use Derafu\Escpos\Enum\BarcodeType;
use Mike42\Escpos\Printer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BarcodeType::class)]
final class BarcodeTypeTest extends TestCase
{
    #[Test]
    public function matchesMike42Constants(): void
    {
        $this->assertSame(Printer::BARCODE_UPCA, BarcodeType::Upca->value);
        $this->assertSame(Printer::BARCODE_UPCE, BarcodeType::Upce->value);
        $this->assertSame(Printer::BARCODE_JAN13, BarcodeType::Jan13->value);
        $this->assertSame(Printer::BARCODE_JAN8, BarcodeType::Jan8->value);
        $this->assertSame(Printer::BARCODE_CODE39, BarcodeType::Code39->value);
        $this->assertSame(Printer::BARCODE_ITF, BarcodeType::Itf->value);
        $this->assertSame(Printer::BARCODE_CODABAR, BarcodeType::Codabar->value);
        $this->assertSame(Printer::BARCODE_CODE93, BarcodeType::Code93->value);
        $this->assertSame(Printer::BARCODE_CODE128, BarcodeType::Code128->value);
    }
}
