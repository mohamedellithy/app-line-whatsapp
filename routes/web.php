<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\NodeJsController;
use App\Http\Controllers\WordPressController;
use App\Http\Controllers\IconWhatsAppController;

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
    return $router->app->version();
});

$router->post('/app-events', [AppController::class, 'make_event']);

$router->post('/wordpress-subscribers',[WordPressController::class,'subscribers']);

$router->post('send-status',[NodeJsController::class,'status_send_message']);

$router->get('whatsapp-icon/{storeId}',[IconWhatsAppController::class,'icon_whatsapp']);