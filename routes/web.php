<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

# 小程序前端接口
//$app->get('/code', 'UserController@oauth');
//
$app->post('/user_info', 'UserController@getUserInfo');
$app->post('/assist', 'UserController@assist');
$app->post('/comment', 'UserController@comment');
