<?php
/**
 * Custom autoloader for DOMPDF and dependencies
 * Replaces Composer autoloader for bundled distribution
 */

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    // Namespace prefixes and their base directories
    $prefixes = [
        'Dompdf\\'  => __DIR__ . '/dompdf/src/',
        'FontLib\\' => __DIR__ . '/php-font-lib/src/FontLib/',
        'Svg\\'     => __DIR__ . '/php-svg-lib/src/Svg/',
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        // Check if class uses this namespace prefix
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        // Get relative class name
        $relative_class = substr($class, $len);

        // Build file path
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Load the file if it exists
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});
