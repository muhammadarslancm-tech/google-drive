<?php

return [
    'app_name' => 'Google Drive Manager',
    'app_url' => getenv('APP_URL') ?: 'http://localhost',
    'storage_path' => __DIR__ . '/../storage',
    'uploads_path' => __DIR__ . '/../storage/uploads',
    'trash_path' => __DIR__ . '/../storage/trash',
    'logs_path' => __DIR__ . '/../storage/logs',
    'token_expiry' => 86400 * 7, // 7 days in seconds
    'max_file_size' => 100 * 1024 * 1024, // 100MB
    'debug' => getenv('APP_DEBUG') === 'true' || false,
];
