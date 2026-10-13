<?php

declare(strict_types=1);

final class Database
{
    public static function connect(): mysqli
    {
        $databaseConfig = require __DIR__ . '/../../config/database.php';
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $connection = mysqli_init();

        if ($connection === false) {
            throw new RuntimeException('Unable to initialize MySQLi.');
        }

        $connection->real_connect(
            $databaseConfig['host'],
            $databaseConfig['username'],
            $databaseConfig['password'],
            $databaseConfig['database']
        );
        $connection->set_charset($databaseConfig['charset']);

        return $connection;
    }
}
