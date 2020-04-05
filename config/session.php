<?php
/**
 * Created by PhpStorm.
 * User: xiaoxiaonan
 * Date: 2019/7/16
 * Time: 16:42
 */

return [
    'driver' => env('SESSION_DRIVER', 'cookie'),
    'lifetime' => 120,//缓存失效时间
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('framework/session'),//file缓存保存路径
    'connection' => null,
    'table' => 'sessions',
    'lottery' => [2, 100],
    'cookie' => 'visitor_session',
    'path' => '/',
    'domain' => null,
    'secure' => false,
];