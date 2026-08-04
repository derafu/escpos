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

use Derafu\Escpos\Constant\ImageSize;
use Mike42\Escpos\Printer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageSize::class)]
final class ImageSizeTest extends TestCase
{
    #[Test]
    public function matchesMike42Constants(): void
    {
        $this->assertSame(Printer::IMG_DEFAULT, ImageSize::STANDARD);
        $this->assertSame(Printer::IMG_DOUBLE_WIDTH, ImageSize::DOUBLE_WIDTH);
        $this->assertSame(Printer::IMG_DOUBLE_HEIGHT, ImageSize::DOUBLE_HEIGHT);
    }
}
