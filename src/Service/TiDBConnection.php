<?php

namespace App\Service;

use mysqli;

class TiDBConnection
{
    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {
            $host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
            $user = '2sXQZXGXGsoK1vx.root';
            $pass = 'qnysm2y695RfCJg9';
            $db = 'farmia_new';
            $caFile = __DIR__ . '/../../cacert.pem';

            self::$connection = new mysqli();
            self::$connection->ssl_set(NULL, NULL, $caFile, NULL, NULL);
            self::$connection->real_connect($host, $user, $pass, $db, 4000, NULL, MYSQLI_CLIENT_SSL);

            if (self::$connection->connect_error) {
                throw new \RuntimeException('TiDB connection failed: ' . self::$connection->connect_error);
            }
        }
        return self::$connection;
    }

    public static function query(string $sql): array
    {
        $conn = self::getConnection();
        $result = $conn->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public static function execute(string $sql): bool
    {
        return self::getConnection()->query($sql);
    }
}