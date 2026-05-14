<?php

namespace App\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Mysqli\Connection as MysqliConnection;

class MysqliSslConnectionFactory
{
    public static function create(array $params): Connection
    {
        $driver = new MysqliDriver();

        return new Connection($params, $driver);
    }
}