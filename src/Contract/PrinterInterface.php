<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\Contract;

use Derafu\Escpos\Constant\ImageSize;
use Derafu\Escpos\Constant\PrintMode;
use Derafu\Escpos\Enum\BarcodeType;
use Derafu\Escpos\Enum\Justification;
use Derafu\Escpos\Enum\Pdf417Option;
use Derafu\Escpos\Exception\UnbufferedConnectorException;
use Derafu\Escpos\ValueObject\RasterImage;

/**
 * The backend `EscposPrinter` needs to actually emit ESC/POS commands.
 *
 * This is the seam between `derafu/escpos`'s own API and whichever library
 * talks to the printer underneath. `Adapter\Mike42Printer` is the only
 * implementation today, but any other implementation (a different backend,
 * a test double) can be swapped in without changing `EscposPrinter` or its
 * consumers.
 */
interface PrinterInterface
{
    public function text(string $text): void;

    public function feed(int $lines = 1): void;

    public function selectPrintMode(int $mode = PrintMode::FONT_A): void;

    public function setJustification(
        Justification $justification = Justification::Left
    ): void;

    public function barcode(
        string $content,
        BarcodeType $type = BarcodeType::Code39,
        int $height = 48
    ): void;

    public function pdf417(
        string $content,
        int $width = 2,
        int $heightMultiplier = 2,
        int $dataColumnCount = 0,
        float $ec = 0.10,
        Pdf417Option $option = Pdf417Option::Standard
    ): void;

    public function image(
        RasterImage $image,
        int $size = ImageSize::STANDARD
    ): void;

    public function cut(): void;

    public function pulse(int $pin = 0, int $onMs = 120, int $offMs = 240): void;

    /**
     * @throws UnbufferedConnectorException If the connector cannot be read
     * back (e.g. it sends directly to a device instead of buffering).
     */
    public function getOutput(): string;

    public function close(): void;
}
