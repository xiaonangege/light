<?php
/**
 * Created by PhpStorm.
 * User: xiaoxiaonan
 * Date: 2019/7/25
 * Time: 13:52
 */

return [
    'driver' => env('MAIL_DRIVER', 'smtp'),
    'host' => env('MAIL_HOST'),
    'port' => env('MAIL_PORT', 587),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
];