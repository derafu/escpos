<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos\Exception;

use Closure;
use Derafu\Escpos\Adapter\Mike42Printer;
use Derafu\Escpos\Exception\ConnectorNotSupportedException;
use Derafu\Escpos\Exception\EscposExceptionInterface;
use Derafu\Escpos\Exception\InvalidImageDataException;
use Derafu\Escpos\Exception\NetworkDeliveryException;
use Derafu\Escpos\Exception\ProfileNotFoundException;
use Derafu\Escpos\Exception\UnbufferedConnectorException;
use Derafu\Escpos\Transport\NetworkTransport;
use Derafu\Escpos\ValueObject\RasterImage;
use Derafu\Translation\Contract\TranslatableInterface;
use InvalidArgumentException;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * The errors of the package are translatable, say what they always did and are
 * still the exceptions that whoever uses the package catches.
 */
#[CoversClass(Mike42Printer::class)]
#[CoversClass(NetworkTransport::class)]
#[CoversClass(RasterImage::class)]
#[UsesClass(ConnectorNotSupportedException::class)]
#[UsesClass(InvalidImageDataException::class)]
#[UsesClass(NetworkDeliveryException::class)]
#[UsesClass(ProfileNotFoundException::class)]
#[UsesClass(UnbufferedConnectorException::class)]
final class EscposExceptionsTest extends TestCase
{
    /**
     * @return array<string, array{Closure(): mixed, class-string<Throwable>, string, string}>
     */
    public static function failuresProvider(): array
    {
        return [
            'image that can not be decoded' => [
                fn () => RasterImage::fromPngData('not a png'),
                InvalidArgumentException::class,
                InvalidImageDataException::class,
                'Could not decode the given PNG data.',
            ],
            'connector that is not supported' => [
                fn () => new Mike42Printer('nope'),
                InvalidArgumentException::class,
                ConnectorNotSupportedException::class,
                'PrintConnector "nope" is not supported.',
            ],
            'profile that is not available' => [
                fn () => new Mike42Printer('memory', 'nope'),
                InvalidArgumentException::class,
                ProfileNotFoundException::class,
                'The ESC/POS printer profile "nope" is not available.',
            ],
            'output of a connector that does not buffer' => [
                function () {
                    $printer = new Mike42Printer(new FilePrintConnector('php://temp'));

                    try {
                        return $printer->getOutput();
                    } finally {
                        $printer->close();
                    }
                },
                RuntimeException::class,
                UnbufferedConnectorException::class,
                'Cannot read the output of a Mike42\Escpos\PrintConnectors\FilePrintConnector connector, it does not buffer data in memory.',
            ],
        ];
    }

    /**
     * @param Closure(): mixed $action
     * @param class-string<Throwable> $parent
     * @param class-string<Throwable> $class
     */
    #[DataProvider('failuresProvider')]
    public function testEveryFailureIsATranslatableErrorThatSaysTheSame(Closure $action, string $parent, string $class, string $message): void
    {
        $exception = null;
        try {
            $action();
        } catch (Throwable $e) {
            $exception = $e;
        }

        $this->assertInstanceOf($class, $exception);
        $this->assertInstanceOf($parent, $exception);
        $this->assertInstanceOf(EscposExceptionInterface::class, $exception);
        $this->assertInstanceOf(TranslatableInterface::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testAConnectionThatFailsIsATranslatableError(): void
    {
        $exception = null;
        try {
            (new NetworkTransport())->send('data', '127.0.0.1', 1, 1);
        } catch (Throwable $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(NetworkDeliveryException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(TranslatableInterface::class, $exception);
        $this->assertMatchesRegularExpression('/^Could not connect to 127\.0\.0\.1:1: .+ \(error \d+\)\.$/', $exception->getMessage());
    }
}
