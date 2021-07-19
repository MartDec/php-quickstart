<?php

namespace Database;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Dotenv\Dotenv;


function connect()
{
    try {

        $env = (new Dotenv())->parse(file_get_contents(__DIR__ . '/../.env'));
        $dbName = $env['DB_DATABASE'];
        $dbHost = $env['DB_HOST'];
        $dbUser = $env['DB_USER'];
        $dbPass = $env['DB_PASSWORD'];

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $dbHost,
            'database'  => $dbName,
            'username'  => $dbUser,
            'password'  => $dbPass,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

    } catch (Exception $e) {

        die("An error occurred while connecting to database: {$e->getMessage()}");

    }
}
