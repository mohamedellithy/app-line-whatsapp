<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\SallaServices\AppEvents;
class AppController extends Controller
{
    //
    public function salla_callback(Request $request){
        Http::post('https://webhook.site/452ffb8f-693f-47a1-b5b8-e1afd328e623',$request->all());
    }

    public function make_event(Request $request){
       Http::get('https://webhook.site/452ffb8f-693f-47a1-b5b8-e1afd328e623',request()->all());
       $event = new AppEvents();
       $result = $event->make_event();
       return $result;
    }
}
