<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Escpos;

use Derafu\Config\Contract\OptionsAwareInterface;
use Derafu\Config\Contract\OptionsInterface;
use Derafu\Config\Trait\OptionsAwareTrait;
use InvalidArgumentException;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\Printer;

/**
 * Wraper of Mike42\Escpos\Printer to send ESC/POS data to a compatible thermal
 * printer.
 */
final class EscposPrinter implements OptionsAwareInterface
{
    // Traits that this class uses.
    use OptionsAwareTrait;

    /**
     * Instance of the printer for ESC/POS.
     *
     * @var Printer
     */
    private Printer $printer;

    /**
     * Printer connector.
     *
     * @var PrintConnector
     */
    private PrintConnector $connector;

    /**
     * Printer profile.
     *
     * @var CapabilityProfile
     */
    private CapabilityProfile $profile;

    /**
     * Printer options.
     *
     * @var array
     */
    protected array $optionsSchema = [
        'connector' => [
            'types' => 'array',
            'schema' => [
                'type' => [
                    'types' => 'string',
                    'default' => 'dummy',
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
                'pin' => [
                    'types' => 'int',
                    'default' => 0,
                ],
                'on_ms' => [
                    'types' => 'int',
                    'default' => 120,
                ],
                'off_ms' => [
                    'types' => 'int',
                    'default' => 240,
                ],
            ],
        ],
        'cut' => [
            'types' => 'bool',
            'default' => true,
        ],
        'specialchars' => [
            'types' => 'bool',
            'default' => true,
        ],
    ];

    /**
     * Constructor of the class.
     *
     * @param array $options Printer configuration.
     */
    public function __construct(array $options = [])
    {
        $this->resolveOptions($options);
    }

    /**
     * Closes the printer when the instance is destroyed.
     */
    public function __destruct()
    {
        if (isset($this->printer)) {
            $this->printer->close();
        }
    }

    /**
     * Prints a text on the printer.
     *
     * @param string $text Text to print on the printer.
     * @param ...$values Values that will be passed to sprintf().
     * @return static
     */
    public function print(string $text, ...$values): static
    {
        $text = sprintf($text, ...$values);

        if (!$this->getOptions()->get('specialchars')) {
            $text = $this->replaceSpecialChars($text);
        }

        $this->getPrinter()->text($text);

        return $this;
    }

    /**
     * Prints a text on the printer with an automatic line break.
     *
     * @param string $text Text to print on the printer.
     * @param ...$values Values that will be passed to sprintf().
     * @return static
     */
    public function println(string $text = '', ...$values): static
    {
        return $this->print($text . "\n", ...$values);
    }

    /**
     * Returns the data in ESC/POS format.
     *
     * @return string
     */
    public function dump(): string
    {
        $connector = $this->getPrinter()->getPrintConnector();

        assert($connector instanceof DummyPrintConnector);

        return $connector->getData();
    }

    /**
     * Sends the commands to finish the print and returns the content.
     *
     * @return string
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

    /**
     * Generates a barcode code.
     *
     * @param string $content Barcode data.
     * @param int $type Barcode type.
     * @param int $height Barcode height.
     * @return static
     */
    public function barcode(
        string $content,
        int $type = Printer::BARCODE_CODE39,
        int $height = 48
    ): static {
        $this->getPrinter()->setBarcodeHeight($height);
        $this->getPrinter()->barcode($content, $type);

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
     * @param float $ec Error correction ratio, from 0.01 to 4.00. Default, 0.10
     * (10%).
     * @param int $options Standard code Printer::PDF417_STANDARD with start and
     * end bars, or truncated code Printer::PDF417_TRUNCATED with only start
     * bars.
     * @return static
     */
    public function pdf417(
        string $content,
        int $width = 2,
        int $heightMultiplier = 2,
        int $dataColumnCount = 0,
        float $ec = 0.10,
        int $options = Printer::PDF417_STANDARD
    ): static {
        $this->getPrinter()->pdf417Code(
            $content,
            $width,
            $heightMultiplier,
            $dataColumnCount,
            $ec,
            $options
        );

        return $this;
    }

    /**
     * Prints an image on the printer.
     *
     * Available size modifiers (can be combined):
     *
     *   - Printer::IMG_DEFAULT (keeps the image in its original size).
     *   - Printer::IMG_DOUBLE_WIDTH (doubles the image width).
     *   - Printer::IMG_DOUBLE_HEIGHT (doubles the image height).
     *
     * @param EscposImage $img The image that will be printed.
     * @param int $size Image size modifier.
     * @return static
     */
    public function image(
        EscposImage $img,
        int $size = Printer::IMG_DEFAULT
    ): static {
        $this->getPrinter()->graphics($img, $size); // Or bitImage() if it fails.

        return $this;
    }

    /**
     * Adds empty lines.
     *
     * @param int $lines
     * @return static
     */
    public function feed(int $lines = 1): static
    {
        $this->getPrinter()->feed($lines);

        return $this;
    }

    /**
     * Selects the print mode that will be used in the following writings on the
     * printer.
     *
     * @param int $mode
     * @return static
     */
    public function selectPrintMode(int $mode = Printer::MODE_FONT_A): static
    {
        $this->getPrinter()->selectPrintMode($mode);

        return $this;
    }

    /**
     * Specifies the justification of the text that will be used in the
     * following writings on the printer.
     *
     * @param int $justification
     * @return static
     */
    public function setJustification(
        int $justification = Printer::JUSTIFY_LEFT
    ): static {
        $this->getPrinter()->setJustification($justification);

        return $this;
    }

    /**
     * Returns the printer.
     *
     * @return Printer
     */
    private function getPrinter(): Printer
    {
        if (!isset($this->printer)) {
            $connector = $this->getConnector();
            $profile = $this->getProfile();
            $this->printer = new Printer($connector, $profile);
        }

        return $this->printer;
    }

    /**
     * Returns the printer connector.
     *
     * @return PrintConnector
     */
    private function getConnector(): PrintConnector
    {
        if (!isset($this->connector)) {
            $connector = $this->getOptions()->get('connector');

            // If the connector is an array then it is the type and the
            // configuration of the same.
            if ($connector instanceof OptionsInterface) {
                $connectorType = $connector->get('type');
                $class = sprintf(
                    '\Mike42\Escpos\PrintConnectors\%sPrintConnector',
                    ucfirst($connectorType)
                );
                switch ($connectorType) {
                    case 'dummy': {
                        $this->connector = new $class();
                        break;
                    }
                    default: {
                        throw new InvalidArgumentException(sprintf(
                            'PrintConnector %s cannot be configured.',
                            $connectorType
                        ));
                    }
                }
            }

            // If the connector is an object then it was already loaded and
            // configured.
            elseif (is_object($connector)) {
                $this->connector = $connector;
            }
        }

        return $this->connector;
    }

    /**
     * Returns the printer profile.
     *
     * @return CapabilityProfile
     */
    private function getProfile(): CapabilityProfile
    {
        if (!isset($this->profile)) {
            $profile = $this->getOptions()->get('profile');

            // If the profile is a string then it is the name of the "profiles"
            // listed in the JSON file:
            // https://github.com/mike42/escpos-php/blob/development/src/Mike42/Escpos/resources/capabilities.json
            if (is_string($profile)) {
                try {
                    $this->profile = CapabilityProfile::load($profile);
                } catch (InvalidArgumentException $e) {
                    $this->getConnector()->finalize();
                    throw new InvalidArgumentException(sprintf(
                        'The ESCPOS printer profile %s is not available.',
                        $profile
                    ));
                }
            }

            // If it is an object then the profile was already loaded.
            elseif (is_object($profile)) {
                $this->profile = $profile;
            }
        }

        return $this->profile;
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
