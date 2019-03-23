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

$router->get('/app-key', function () use ($router) {
    
    $path = base_path('.env');

	if (file_exists($path)) {
		
		file_put_contents($path, str_replace(
	    	'APP_KEY=', 'APP_KEY='.str_random(32), file_get_contents($path)
		));
	}
});

$router->get('/jwt-secret', function () use ($router) {
    
    $path = base_path('.env');

	if (file_exists($path)) {
		
		file_put_contents($path, str_replace(
	    	'JWT_SECRET=', 'JWT_SECRET='.str_random(32), file_get_contents($path)
		));
	}
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