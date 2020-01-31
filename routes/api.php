<?php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
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
$router->get('/', function () use ($router) {
    return  Str::random (32);
});


$router->get('/key', function () use ($router) {
    return  Str::random (32);
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/user/store', ['as' => 'user.store', 'uses' => 'UserController@store']);
    $router->post('/user/login', ['as' => 'user.login', 'uses' => 'UserController@login']);
    $router->post('/user/logout', ['as' => 'user.logout', 'uses' => 'UserController@logout']);

});
