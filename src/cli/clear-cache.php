<?php
/**
 * Cache clearing script
 */
require_once __DIR__ . '/../bootstrap.php';

echo "Clearing application cache...\n";

try {
    // Define cache directories
    $cacheDirs = [
        BASE_PATH . '/cache/templates',
        BASE_PATH . '/cache/data',
    ];
    
    // Create cache directories if they don't exist
    foreach ($cacheDirs as $dir) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                echo "Warning: Could not create directory: $dir\n";
            }
        }
    }
    
    // Clear cache files
    foreach ($cacheDirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    echo ".";
                }
            }
        }
    }
    
    echo "\nCache cleared successfully.\n";
    
} catch (Exception $e) {
    echo "\nError clearing cache: " . $e->getMessage() . "\n";
    exit(1);
}
