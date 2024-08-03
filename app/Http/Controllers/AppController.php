<?php

namespace App\Http\Controllers;

use App\Models\SallaWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Cache;
use App\Services\SallaServices\AppEvents;

class AppController extends Controller
{
    public function make_event(Request $request){
        $event = $request->all();
        $event_id = isset($event['data']) ? (isset($event['data']['id']) ? $event['data']['id'] : rand(1,1000)) : rand(1,1000);
        $lock  = Cache::lock("event_no_".$event_id,2);
        if($lock->get()){
            $event = new AppEvents();
            $result = $event->make_event($request->all());
            return $result;
        }
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
