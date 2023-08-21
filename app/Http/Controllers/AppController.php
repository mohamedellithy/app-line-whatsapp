<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\SallaServices\AppEvents;
class AppController extends Controller
{
    //
    public function salla_callback(Request $request){
        Http::post('https://webhook.site/f032ba41-f451-4aba-a8b3-a97fbff114de',$request->all());
    }

    public function make_event(Request $request){
       Http::get('https://webhook.site/f032ba41-f451-4aba-a8b3-a97fbff114de',request()->all());
       $event = new AppEvents();
       $result = $event->make_event();
       return $result;
    }
}
