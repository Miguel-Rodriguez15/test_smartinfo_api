<?php
class DatabaseConfig {
    public static $settings = [
        'host'      => 'localhost',
        'port'      => '5432',
        'dbname'    => 'laberinto',
        'user'      => 'postgres',
        'password'  => 'postgres098',
        'options'   => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    ];
}