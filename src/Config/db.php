<?php
return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? '3306', 
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'dbname' => $_ENV['DB_NAME'],
    'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql'
];
