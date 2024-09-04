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
        set_time_limit(0);
        ini_set('max_execution_time', 0); //0=NOLIMIT

        $event = file_get_contents('php://input');
        // $client   = new \GuzzleHttp\Client();
        // $send_result         = $client->request("POST","https://typedwebhook.tools/webhook/87b7581e-3bb8-4fa4-a5d1-ba24f4024497",[
        //     'form_params' => [
        //         'body' => $event
        //     ]
        // ]);

        \Log::info(json_decode($event,true));
        
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
