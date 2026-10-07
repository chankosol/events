<?php
return [
    'host'    => getenv('DB_HOST')    ?: 'localhost',
    'port'    => getenv('DB_PORT')    ?: '3306',
    'dbname'  => getenv('DB_NAME')    ?: 'workshopos',
    'user'    => getenv('DB_USER')    ?: 'root',
    'pass'    => getenv('DB_PASS')    ?: '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];
