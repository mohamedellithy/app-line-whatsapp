<?php

use App\Exports\NumbersExport;
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
    \Log::info('bb');
    return $router->app->version();
});

$router->post('/app-events', 'AppController@make_event');

$router->post('/wordpress-subscribers','WordPressController@subscribers');

$router->post('/api/send-status','NodeJsController@status_send_message');

$router->get('/api/booking-sheet','BookingSheetController@booking_sheet');

$router->get('/api/sheet-service/{user_id}','BookingSheetController@booking_sheet');

$router->group(['middleware' => 'cors'], function() use ($router) {
    $router->post('/tester-web','BookingSheetController@auto_replay');
    $router->get('/tester-web','BookingSheetController@auto_replay');
});

// $router->group(['middleware' => 'cors'], function () use ($router) {
//     $router->get('/api/whatsapp-icon/{storeId}','IconWhatsAppController@icon_whatsapp');
// });

$router->get('/export-contacts',function(){
    return (new NumbersExport)->download('numbers-salla.xlsx');
});