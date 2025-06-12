<?php
/**
 * Application Configuration
 */
return [
    'name' => getenv('APP_NAME') ?: 'Dev Test Evaluation',
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'debug' => getenv('APP_DEBUG') === 'true',
    'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
];
