<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

// Include the autoloader (adjust this path to match your environment).
require_once __DIR__ . '/../vendor/autoload.php';

use Derafu\Escpos\EscposPrinter;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;

// Initialize the printer with default settings. Uses a dummy connector by
// default, which allows us to capture the ESC/POS commands.
$printer = new EscposPrinter([
    'connector' => ['type' => 'dummy'],
    'profile' => 'default',
    'cut' => true,
    'specialchars' => true,
]);

// ----------------------------------------------------------------------------
// 1. Image printing.
// ----------------------------------------------------------------------------

$logo = EscposImage::load(__DIR__ . '/dummy-logo.png');
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->feed(1);
$printer->image($logo);
$printer->feed(1);

// ----------------------------------------------------------------------------
// 2. Basic text printing.
// ----------------------------------------------------------------------------

// Center align the header.
$printer->setJustification(Printer::JUSTIFY_CENTER);

// Print shop name with double width and height.
$printer->selectPrintMode(
    Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH
);
$printer->println("EXAMPLE SHOP");

// Reset to normal print mode.
$printer->selectPrintMode(Printer::MODE_FONT_A);
$printer->println("123 Example Street");
$printer->println("City, Country");
$printer->println("Tel: (123) 456-7890");

// Add date and time.
$printer->println(date('Y-m-d H:i:s'));

// Divider.
$printer->feed(1);
$printer->println("================================================");
$printer->feed(1);

// ----------------------------------------------------------------------------
// 3. Item list with different justifications.
// ----------------------------------------------------------------------------

// Left justify for item descriptions.
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->println("SALES RECEIPT");
$printer->println("");

// Sample item list - using sprintf for alignment.
$printer->println(sprintf("%-29s %6s %11s", "ITEM", "QTY", "PRICE"));
$printer->println("------------------------------------------------");

// Print items with right-aligned values.
$items = [
    ["T-Shirt XL", 2, 19.99],
    ["Coffee Mug", 1, 5.99],
    ["Notebook", 3, 4.49],
];

$total = 0;
foreach ($items as $item) {
    list($name, $qty, $price) = $item;
    $subtotal = $qty * $price;
    $total += $subtotal;

    // Format the line with fixed spacing.
    $printer->print("%-29s %6.2f %11.2f\n", $name, $qty, $price);
}

// Subtotal and tax.
$printer->println("------------------------------------------------");
$tax = $total * 0.10; // Assume 10% tax.

// Right-align the totals.
$printer->setJustification(Printer::JUSTIFY_RIGHT);
$printer->print("SUBTOTAL: %11.2f\n", $total);
$printer->print("TAX 10%%: %11.2f\n", $tax);
$printer->println("------------------------------------------------");

// Print total with emphasis (bold).
$printer->selectPrintMode(Printer::MODE_EMPHASIZED);
$printer->print("TOTAL: %11.2f\n", $total + $tax);
$printer->selectPrintMode(Printer::MODE_FONT_A);

// Payment method.
$printer->println("");
$printer->println("Payment: Credit Card");
$printer->println("Card: XXXX-XXXX-XXXX-1234");

// ----------------------------------------------------------------------------
// 4. Barcode printing.
// ----------------------------------------------------------------------------

$printer->feed(1);
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->println("Transaction ID: TRX12345");

// Print a CODE39 barcode.
$printer->barcode("TRX12345", Printer::BARCODE_CODE39);

// ----------------------------------------------------------------------------
// 5. 2D barcode (PDF417).
// ----------------------------------------------------------------------------

$printer->feed(1);
$printer->println("Scan for more information:");

// Create a PDF417 2D barcode with transaction info.
$pdf417data = "TRX:12345|DATE:" . date('Ymd') . "|TOTAL:" . number_format($total + $tax, 2);
$pdf417data = file_get_contents(__DIR__ . '/../LICENSE');
$printer->pdf417($pdf417data);

// ----------------------------------------------------------------------------
// 6. Special characters handling.
// ----------------------------------------------------------------------------

$printer->feed(1);
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->println("Special Chars: áéíóúñÑ");

// ----------------------------------------------------------------------------
// 7. Footer with terms and conditions.
// ----------------------------------------------------------------------------

$printer->feed(1);
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->println("Thank you for your purchase!");
$printer->println("");
$printer->selectPrintMode(Printer::MODE_FONT_B); // Smaller font.
$printer->println("This is not a tax invoice.");
$printer->println("Exchange within 30 days with receipt.");
$printer->println("Terms and conditions apply.");

// ----------------------------------------------------------------------------
// 8. Get the ESC/POS data.
// ----------------------------------------------------------------------------

// Finalize the receipt (adds final line breaks, cuts paper).
$escposData = $printer->end();

// Output the ESC/POS data to stdout. When redirected to a file or piped to
// netcat, this will be sent to the printer.
echo $escposData;
