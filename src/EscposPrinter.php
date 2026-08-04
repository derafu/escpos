<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2025-2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos;

use Derafu\Config\Contract\OptionsAwareInterface;
use Derafu\Config\Contract\OptionsInterface;
use Derafu\Config\Trait\OptionsAwareTrait;
use Derafu\Escpos\Adapter\Mike42Printer;
use Derafu\Escpos\Constant\ImageSize;
use Derafu\Escpos\Constant\PrintMode;
use Derafu\Escpos\Contract\PrinterInterface;
use Derafu\Escpos\Enum\BarcodeType;
use Derafu\Escpos\Enum\Justification;
use Derafu\Escpos\Enum\Pdf417Option;
use Derafu\Escpos\ValueObject\RasterImage;

/**
 * Facade to send ESC/POS data to a compatible thermal printer.
 *
 * This class never talks to the underlying ESC/POS library directly — that
 * is the job of the `PrinterInterface` it delegates to (by default,
 * `Adapter\Mike42Printer`). This class only owns: option resolution, the
 * sprintf/line-break convenience of `print()`/`println()`, the
 * `specialchars` text preprocessing, and the `end()` workflow (finish,
 * optionally cut and pulse, return the output).
 */
final class EscposPrinter implements OptionsAwareInterface
{
    // Traits that this class uses.
    use OptionsAwareTrait;

    private readonly PrinterInterface $printer;

    /** @var array */
    protected array $optionsSchema = [
        'connector' => [
            'types' => 'array',
            'schema' => [
                'type' => [
                    'types' => 'string',
                    'default' => 'memory',
                ],
            ],
        ],
        'profile' => [
            'types' => 'string',
            'default' => 'default',
        ],
        'pulse' => [
            'types' => 'array',
            'schema' => [
                'pin' => ['types' => 'int', 'default' => 0],
                'on_ms' => ['types' => 'int', 'default' => 120],
                'off_ms' => ['types' => 'int', 'default' => 240],
            ],
        ],
        'cut' => ['types' => 'bool', 'default' => true],
        'specialchars' => ['types' => 'bool', 'default' => true],
    ];

    /**
     * @param array $options Printer configuration.
     * @param PrinterInterface|null $printer Backend to delegate to. Mainly
     * useful for tests; consumers normally leave this to its default
     * (a `Mike42Printer` built from $options).
     */
    public function __construct(
        array $options = [],
        ?PrinterInterface $printer = null,
    ) {
        $this->resolveOptions($options);
        $this->printer = $printer ?? new Mike42Printer(
            $this->resolveConnectorOption(),
            $this->getOptions()->get('profile'),
        );
    }

    /**
     * Closes the printer when the instance is destroyed.
     */
    public function __destruct()
    {
        $this->printer->close();
    }

    public function print(string $text, ...$values): static
    {
        $text = sprintf($text, ...$values);

        if (!$this->getOptions()->get('specialchars')) {
            $text = $this->replaceSpecialChars($text);
        }

        $this->printer->text($text);

        return $this;
    }

    public function println(string $text = '', ...$values): static
    {
        return $this->print($text . "\n", ...$values);
    }

    /**
     * Returns the data in ESC/POS format printed so far.
     */
    public function dump(): string
    {
        return $this->printer->getOutput();
    }

    /**
     * Sends the commands to finish the print and returns the content.
     */
    public function end(): string
    {
        // Add final line breaks.
        $this->print("\n\n");

        // Cut paper and open the paper tray if it exists and is configured.
        if ($this->getOptions()->get('cut')) {
            $this->printer->cut();
            $this->printer->pulse(
                $this->getOptions()->get('pulse.pin'),
                $this->getOptions()->get('pulse.on_ms'),
                $this->getOptions()->get('pulse.off_ms')
            );
        }

        // Return the content with the printer ESC/POS code.
        return $this->dump();
    }

    public function barcode(
        string $content,
        BarcodeType $type = BarcodeType::Code39,
        int $height = 48
    ): static {
        $this->printer->barcode($content, $type, $height);

        return $this;
    }

    /**
     * Generates a 2D data code using the PDF417 standard.
     *
     * @param string $content Text or numbers that will be stored in the code.
     * @param int $width Module width (pixel).
     * @param int $heightMultiplier Module height multiplier.
     * @param int $dataColumnCount Number of data columns to use. With the value
     * by default 0, the columns will be calculated automatically. Smaller
     * numbers result in a narrower code, allowing larger pixel sizes. Larger
     * numbers require smaller pixel sizes.
     * @param float $ec Error correction ratio, from 0.01 to 4.00. Default,
     * 0.10 (10%).
     */
    public function pdf417(
        string $content,
        int $width = 2,
        int $heightMultiplier = 2,
        int $dataColumnCount = 0,
        float $ec = 0.10,
        Pdf417Option $option = Pdf417Option::Standard
    ): static {
        $this->printer->pdf417(
            $content,
            $width,
            $heightMultiplier,
            $dataColumnCount,
            $ec,
            $option
        );

        return $this;
    }

    /**
     * Prints an image on the printer.
     *
     * Available size modifiers (can be combined):
     *
     *   - ImageSize::STANDARD (keeps the image in its original size).
     *   - ImageSize::DOUBLE_WIDTH (doubles the image width).
     *   - ImageSize::DOUBLE_HEIGHT (doubles the image height).
     */
    public function image(
        RasterImage $image,
        int $size = ImageSize::STANDARD
    ): static {
        $this->printer->image($image, $size);

        return $this;
    }

    /**
     * Adds empty lines.
     */
    public function feed(int $lines = 1): static
    {
        $this->printer->feed($lines);

        return $this;
    }

    /**
     * Selects the print mode that will be used in the following writings on
     * the printer.
     */
    public function selectPrintMode(int $mode = PrintMode::FONT_A): static
    {
        $this->printer->selectPrintMode($mode);

        return $this;
    }

    /**
     * Specifies the justification of the text that will be used in the
     * following writings on the printer.
     */
    public function setJustification(
        Justification $justification = Justification::Left
    ): static {
        $this->printer->setJustification($justification);

        return $this;
    }

    /**
     * Extracts the connector type/instance from the resolved options.
     *
     * The `connector` option is declared as an array (`['type' => ...]`),
     * so it normally arrives wrapped in an `OptionsInterface`. It may also
     * be an already-built connector instance passed directly.
     */
    private function resolveConnectorOption(): string|object
    {
        $connector = $this->getOptions()->get('connector');

        if ($connector instanceof OptionsInterface) {
            return $connector->get('type');
        }

        return $connector;
    }

    /**
     * Replaces special characters in the string by characters that do not
     * generate problems when printing because they have special encoding.
     *
     * @param string $string String to replace special characters.
     * @return string String with replaced special characters.
     */
    private function replaceSpecialChars(string $string): string
    {
        $from = [
            'á','À','Á','Â','Ã','Ä','Å',
            'é','È','É','Ê','Ë',
            'í','Ì','Í','Î','Ï',
            'ó','Ò','Ó','Ô','Õ','Ö',
            'ú','Ù','Ú','Û','Ü',
            'ß','Ç','ñ','Ñ', '°',
        ];

        $to = [
            'a','A','A','A','A','A','A',
            'e','E','E','E','E',
            'i','I','I','I','I',
            'o','O','O','O','O','O',
            'u','U','U','U','U',
            'B','C','n','N', '',
        ];

        return str_replace($from, $to, $string);
    }
}
