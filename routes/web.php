<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordPressController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->to('https://line.sa');
});


// Route::get('/salla-callback', [App\Http\Controllers\AppController::class, 'salla_callback']);
Route::post('/app-events', [App\Http\Controllers\AppController::class, 'make_event']);

Route::post('/wordpress-subscribers',[WordPressController::class,'subscribers']);
