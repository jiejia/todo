<?php

use Illuminate\Support\Str;
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
    $router->post('/user/detail', ['as' => 'user.detail', 'uses' => 'UserController@detail']);
    $router->post('/user/update', ['as' => 'user.update', 'uses' => 'UserController@update']);
    $router->post('/user/login', ['as' => 'user.login', 'uses' => 'UserController@login']);
    $router->post('/user/logout', ['as' => 'user.logout', 'uses' => 'UserController@logout']);

    $router->post('/task/create-or-update', ['as' => 'task.createOrUpdate', 'uses' => 'TaskController@createOrUpdate']);
    $router->post('/task/list-or-search', ['as' => 'task.listOrSearch', 'uses' => 'TaskController@listOrSearch']);
    $router->post('/task/detail', ['as' => 'task.detail', 'uses' => 'TaskController@detail']);
    $router->post('/task/delete', ['as' => 'task.delete', 'uses' => 'TaskController@delete']);

    $router->post('/task/category/create-or-update', ['as' => 'task_category.createOrUpdate', 'uses' => 'Task\CategoryController@createOrUpdate']);
    $router->post('/task/category/list-or-search', ['as' => 'task_category.listOrSearch', 'uses' => 'Task\CategoryController@listOrSearch']);
    $router->post('/task/category/detail', ['as' => 'task_category.detail', 'uses' => 'Task\CategoryController@detail']);
    $router->post('/task/category/delete', ['as' => 'task_category.delete', 'uses' => 'Task\CategoryController@delete']);

});
