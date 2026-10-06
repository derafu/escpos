<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos\Adapter;

use Derafu\Escpos\Constant\ImageSize;
use Derafu\Escpos\Constant\PrintMode;
use Derafu\Escpos\Contract\PrinterInterface;
use Derafu\Escpos\Enum\BarcodeType;
use Derafu\Escpos\Enum\Justification;
use Derafu\Escpos\Enum\Pdf417Option;
use Derafu\Escpos\Exception\ConnectorNotSupportedException;
use Derafu\Escpos\Exception\ProfileNotFoundException;
use Derafu\Escpos\Exception\UnbufferedConnectorException;
use Derafu\Escpos\ValueObject\RasterImage;
use InvalidArgumentException;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\GdEscposImage;
use Mike42\Escpos\PrintConnectors\MemoryPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\Printer;

/**
 * The only class in this package allowed to `use Mike42\Escpos\*`.
 *
 * Everything `EscposPrinter` needs from a real ESC/POS backend is
 * implemented here, on top of `mike42/escpos-php`. If that library is ever
 * replaced, this is the only class that needs to change.
 */
final class Mike42Printer implements PrinterInterface
{
    private readonly PrintConnector $connector;

    private readonly Printer $printer;

    /**
     * @param string|object $connector Connector type name (currently only
     * `'memory'` is supported), or an already-built connector instance.
     * @param string|object $profile Capability profile name, or an
     * already-loaded profile instance.
     */
    public function __construct(
        string|object $connector = 'memory',
        string|object $profile = 'default',
    ) {
        $this->connector = $this->resolveConnector($connector);
        $this->printer = new Printer(
            $this->connector,
            $this->resolveProfile($profile),
        );
    }

    public function text(string $text): void
    {
        $this->printer->text($text);
    }

    public function feed(int $lines = 1): void
    {
        $this->printer->feed($lines);
    }

    public function selectPrintMode(int $mode = PrintMode::FONT_A): void
    {
        $this->printer->selectPrintMode($mode);
    }

    public function setJustification(
        Justification $justification = Justification::Left
    ): void {
        $this->printer->setJustification($justification->value);
    }

    public function barcode(
        string $content,
        BarcodeType $type = BarcodeType::Code39,
        int $height = 48
    ): void {
        $this->printer->setBarcodeHeight($height);
        $this->printer->barcode($content, $type->value);
    }

    public function pdf417(
        string $content,
        int $width = 2,
        int $heightMultiplier = 2,
        int $dataColumnCount = 0,
        float $ec = 0.10,
        Pdf417Option $option = Pdf417Option::Standard
    ): void {
        $this->printer->pdf417Code(
            $content,
            $width,
            $heightMultiplier,
            $dataColumnCount,
            $ec,
            $option->value
        );
    }

    public function image(
        RasterImage $image,
        int $size = ImageSize::STANDARD
    ): void {
        $mike42Image = new GdEscposImage();
        // Mike42's docblock still says `resource`, but its implementation
        // explicitly checks for and accepts a GdImage instance too — the
        // docblock predates PHP 8's GD object migration, this is not a
        // real type mismatch.
        // @phpstan-ignore argument.type
        $mike42Image->readImageFromGdResource($image->getResource());
        $this->printer->graphics($mike42Image, $size);
    }

    public function cut(): void
    {
        $this->printer->cut();
    }

    public function pulse(int $pin = 0, int $onMs = 120, int $offMs = 240): void
    {
        $this->printer->pulse($pin, $onMs, $offMs);
    }

    public function getOutput(): string
    {
        if (!$this->connector instanceof MemoryPrintConnector) {
            throw new UnbufferedConnectorException([
                'Cannot read the output of a {connector} connector, it does not buffer data in memory.',
                'connector' => $this->connector::class,
            ]);
        }

        return $this->connector->getData();
    }

    public function close(): void
    {
        $this->printer->close();
    }

    private function resolveConnector(string|object $connector): PrintConnector
    {
        if (is_object($connector)) {
            return $connector;
        }

        return match ($connector) {
            'memory' => new MemoryPrintConnector(),
            default => throw new ConnectorNotSupportedException([
                'PrintConnector "{connector}" is not supported.',
                'connector' => $connector,
            ]),
        };
    }

    private function resolveProfile(string|object $profile): CapabilityProfile
    {
        if (is_object($profile)) {
            return $profile;
        }

        try {
            return CapabilityProfile::load($profile);
        } catch (InvalidArgumentException) {
            $this->connector->finalize();
            throw new ProfileNotFoundException([
                'The ESC/POS printer profile "{profile}" is not available.',
                'profile' => $profile,
            ]);
        }
    }
}
