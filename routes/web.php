<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
    return 'Hello, api2. ' . $router->app->version();
});

$router->group(['prefix' => 'user'], function () use ($router) {
    $router->post('login', 'MainController@login');
    $router->post('register', 'MainController@register');

    $router->group(['middleware' => ['auth:api']], function () use ($router) {
        $router->post('refresh-access-token', 'MainController@refreshAccessToken');
    });
});

$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->get('{entity}/logout', 'MainController@logout');

    $router->post('{entity}/', 'MainController@create');
    $router->post('{entity}/create', 'MainController@create');

    $router->delete('{entity}/{id}', 'MainController@delete');
    $router->delete('{entity}/delete/{id}', 'MainController@delete');

    $router->put('{entity}/{id}', 'MainController@update');
    $router->put('{entity}/update/{id}', 'MainController@update');

    $router->get('{entity}/', 'MainController@getItems');
    $router->get('{entity}/get-items', 'MainController@getItems');

    $router->get('{entity}/{id}', 'MainController@getItem');
    $router->get('{entity}/get-item/{id}', 'MainController@getItem');

    $router->get('/user/me', function () use ($router) {
       return [
           'id' => auth()->user()->id,
           'name' => auth()->user()->name,
       ];
    });
});
