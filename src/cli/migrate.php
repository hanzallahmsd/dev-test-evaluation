<?php
/**
 * Database migration script
 */
require_once __DIR__ . '/../bootstrap.php';

echo "Running database migrations...\n";

try {
    // Get database connection
    $db = db();
    
    // Read schema file
    $schemaPath = BASE_PATH . '/src/config/schema.sql';
    $schema = file_get_contents($schemaPath);
    
    if (!$schema) {
        throw new Exception("Could not read schema file: $schemaPath");
    }
    
    // Split SQL statements
    $statements = array_filter(
        array_map(
            'trim',
            explode(';', $schema)
        )
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
            echo ".";
        }
    }
    
    echo "\nDatabase migration completed successfully.\n";
    
} catch (Exception $e) {
    echo "\nError during migration: " . $e->getMessage() . "\n";
    exit(1);
}
