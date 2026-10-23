<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos\Adapter;

use Derafu\Escpos\Adapter\Mike42Printer;
use Derafu\Escpos\Constant\PrintMode;
use Derafu\Escpos\Enum\BarcodeType;
use Derafu\Escpos\Enum\Justification;
use Derafu\Escpos\Exception\ConnectorNotSupportedException;
use Derafu\Escpos\Exception\ProfileNotFoundException;
use Derafu\Escpos\ValueObject\RasterImage;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\MemoryPrintConnector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Mike42Printer::class)]
#[UsesClass(RasterImage::class)]
#[UsesClass(ConnectorNotSupportedException::class)]
#[UsesClass(ProfileNotFoundException::class)]
final class Mike42PrinterTest extends TestCase
{
    public function testRejectsAnObjectThatIsNotAPrintConnector(): void
    {
        $this->expectException(ConnectorNotSupportedException::class);
        $this->expectExceptionMessage('The connector must implement PrintConnector; stdClass given.');
        new Mike42Printer(new stdClass());
    }

    public function testRejectsAnIncompatibleProfileAndFinalizesTheConnector(): void
    {
        $connector = new MemoryPrintConnector();
        $this->expectException(ProfileNotFoundException::class);
        $this->expectExceptionMessage('The printer profile must be a CapabilityProfile instance; stdClass given.');
        // An unfinalized real connector emits a notice when destroyed, which
        // makes this test fail under the suite's strict notice handling.
        new Mike42Printer($connector, new stdClass());
    }

    public function testAcceptsARealConnectorAndLoadedProfile(): void
    {
        $connector = new MemoryPrintConnector();
        $printer = new Mike42Printer($connector, CapabilityProfile::load('default'));
        try {
            $printer->text('Hello');
            $this->assertSame("\x1b@Hello", $printer->getOutput());
            $this->assertSame($printer->getOutput(), $connector->getData());
        } finally {
            $printer->close();
        }
    }

    public function testRendersACompleteReceiptThroughTheRealBackend(): void
    {
        $printer = new Mike42Printer();
        try {
            $printer->selectPrintMode(PrintMode::FONT_A | PrintMode::EMPHASIZED);
            $printer->setJustification(Justification::Center);
            $printer->text("ACME S.A.\n");
            $printer->selectPrintMode(PrintMode::FONT_B);
            $printer->setJustification(Justification::Left);
            $printer->text("R.U.T.: 76192083-9\n");
            $printer->text("Folio N°: 123\n");
            $printer->feed(1);
            $printer->setJustification(Justification::Right);
            $printer->text("Total: $19.990\n");
            $printer->setJustification(Justification::Center);
            $printer->feed(1);
            $printer->barcode('ABC123', BarcodeType::Code39, 48);
            $printer->feed(1);
            $printer->pdf417('LIBREDTE-TIMBRE-DATA');

            $gd = imagecreatetruecolor(8, 8);
            imagefill($gd, 0, 0, imagecolorallocate($gd, 255, 255, 255));
            imagesetpixel($gd, 0, 0, imagecolorallocate($gd, 0, 0, 0));
            imagesetpixel($gd, 7, 7, imagecolorallocate($gd, 0, 0, 0));
            $printer->image(new RasterImage($gd));
            $printer->feed(2);
            $printer->text("\n\n");
            $printer->cut();
            $printer->pulse();

            $expected = file_get_contents(__DIR__ . '/../../fixtures/escpos-printer-sequence.escpos');
            $this->assertNotFalse($expected);
            $this->assertSame($expected, $printer->getOutput());
        } finally {
            $printer->close();
        }
    }
}
