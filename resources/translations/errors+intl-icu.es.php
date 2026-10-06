<?php

declare(strict_types=1);

/**
 * Derafu: ESCPOS - PHP Library for ESC/POS Printers.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

return [
    'Could not decode the given PNG data.' =>
        'No se pudieron decodificar los datos PNG entregados.',
    'Could not connect to {host}:{port}: {error} (error {code}).' =>
        'No se pudo conectar a {host}:{port}: {error} (error {code}).',
    'Could not write all {bytes} bytes to {host}:{port}.' =>
        'No se pudieron escribir los {bytes} bytes en {host}:{port}.',
    'Cannot read the output of a {connector} connector, it does not buffer data in memory.' =>
        'No se puede leer la salida de un conector {connector}, no guarda los datos en memoria.',
    'PrintConnector "{connector}" is not supported.' =>
        'El PrintConnector "{connector}" no está soportado.',
    'The ESC/POS printer profile "{profile}" is not available.' =>
        'El perfil de impresora ESC/POS "{profile}" no está disponible.',
];
