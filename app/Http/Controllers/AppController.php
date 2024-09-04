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

        // $client   = new \GuzzleHttp\Client();
        // $send_result         = $client->request("POST","https://webhook.site/c97cac23-89da-4179-9829-9607dd7944e1",[
        //     'form_params' => [
        //         'body' => json_decode(file_get_contents('php://input'),true)
        //     ]
        // ]);

        $event = json_decode(file_get_contents('php://input'),true);
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
