<?php

return [
    'host' => getenv('MONGODB_HOST') ?: 'localhost',
    'port' => (int)(getenv('MONGODB_PORT') ?: 27017),
    'database' => getenv('MONGODB_DATABASE') ?: 'google_drive',
    'username' => getenv('MONGODB_USERNAME') ?: '',
    'password' => getenv('MONGODB_PASSWORD') ?: '',
];

// mongodb://localhost:27017/
