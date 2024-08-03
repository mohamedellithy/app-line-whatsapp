<?php

namespace App\Http\Controllers;

use App\Models\SallaWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\SallaServices\AppEvents;
class AppController extends Controller
{
    public function make_event(Request $request){
        $event = new AppEvents();
        $result = $event->make_event($request->all());
        return $result;
        // $salla_webhooks = SallaWebhook::updateOrCreate([
        //     'event' => json_encode($request->all())
        // ]);
        
        // if($salla_webhooks){
        //     return response()->json([
        //         'status' => 200
        //     ]);
        // }
    }
}
