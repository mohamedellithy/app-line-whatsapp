<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AppController extends Controller
{
    //

    public function salla_callback(Request $request){
        Http::post('https://webhook.site/19694e58-fa42-41d5-a247-2187b0718cf7',$request->all());
    }
    public function make_event(Request $request){
        Http::get('https://webhook.site/19694e58-fa42-41d5-a247-2187b0718cf7');
    }
}
