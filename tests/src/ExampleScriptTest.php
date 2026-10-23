<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsEscpos;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class ExampleScriptTest extends TestCase
{
    /** @return iterable<string, array{bool, string}> */
    public static function missingFiles(): iterable
    {
        yield 'logo' => [false, "Could not read the example logo.\n"];
        yield 'license' => [true, "Could not read the license for the PDF417 example.\n"];
    }

    #[DataProvider('missingFiles')]
    public function testMissingFilesFailWithAClearMessageAndNoReceipt(bool $withLogo, string $message): void
    {
        $root = dirname(__DIR__, 2);
        $directory = sys_get_temp_dir() . '/escpos-example-' . bin2hex(random_bytes(8));
        $this->assertTrue(mkdir($directory));
        try {
            $this->assertTrue(mkdir($directory . '/examples'));
            $this->assertTrue(symlink($root . '/vendor', $directory . '/vendor'));
            $this->assertTrue(copy($root . '/examples/full-example.php', $directory . '/examples/full-example.php'));
            if ($withLogo) {
                $this->assertTrue(copy($root . '/examples/dummy-logo.png', $directory . '/examples/dummy-logo.png'));
            }

            $process = proc_open(
                [PHP_BINARY, $directory . '/examples/full-example.php'],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes
            );
            $this->assertIsResource($process);
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $status = proc_close($process);

            $this->assertSame(1, $status);
            $this->assertSame('', $stdout);
            $this->assertSame($message, $stderr);
        } finally {
            foreach (['examples/full-example.php', 'examples/dummy-logo.png', 'vendor'] as $path) {
                if (file_exists($directory . '/' . $path) || is_link($directory . '/' . $path)) {
                    unlink($directory . '/' . $path);
                }
            }
            if (is_dir($directory . '/examples')) {
                rmdir($directory . '/examples');
            }
            rmdir($directory);
        }
    }
}
