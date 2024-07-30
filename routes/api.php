<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NodeJsController;
use App\Http\Controllers\IconWhatsAppController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('send-status',[NodeJsController::class,'status_send_message']);

Route::get('whatsapp-icon/{storeId}',[IconWhatsAppController::class,'icon_whatsapp']);