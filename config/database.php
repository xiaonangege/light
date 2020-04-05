<?php
/**
 * Created by PhpStorm.
 * User: bitzo
 * Date: 2019/2/22
 * Time: 21:45
 */
//require_once "/opt/ci123/www/html/sc-edu/com/inc/config.php";

return [
    'fetch' => PDO::FETCH_CLASS,
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'name'      => 'mysql',
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'port'      => 3306,
            'database'  => 'jhy',
            'username'  => 'debian-sys-maint',
            'password'  => 'dsgF9nGcJkMfP5U2',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => 'light_',
            'timezone'  => env('DB_TIMEZONE','+08:00'),
            'strict'    => false,
        ],
    ],
];
