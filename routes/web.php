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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post(
    'api/v1.0/auth/login','UserController@authenticate'
);

$router->post(
    'api/v1.0/auth/register','UserController@register'
);

$router->group(
    [
    	'middleware' => 'jwt.auth',
    	'prefix' => 'api/v1.0'
    ],
    function() use ($router) {

    	$router->get('me','UserController@me');
});