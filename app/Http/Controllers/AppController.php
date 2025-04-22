<?php

namespace App\Http\Controllers;

use App\Models\SallaWebhook;
use Illuminate\Http\Request;
use App\Jobs\SallaEventProcess;
use Illuminate\Support\Facades\Http;
use App\Services\SallaServices\AppEvents;
use Illuminate\Support\Facades\Cache;
use App\Services\AppSettings\CheckMerchant;

class AppController extends Controller
{
    public function make_event(Request $request){
        set_time_limit(-1);
        ini_set('max_execution_time', 0); //0=NOLIMIT

        $event_content = file_get_contents('php://input');
        $event         = json_decode($event_content,true);
        if(is_string($event)){
            $event = json_decode($event,true);
        }

        // validate merchant
        if(isset($event) && !in_array($event['event'],[
            'app.store.authorize','app.subscription.started',
            'app.subscription.renewed','app.settings.updated'
        ])){
            CheckMerchant::Validate($event);
        }

        // $event_name  = (isset($this->event['event']) ? $event['event'] : '');
        // $merchant_id = (isset($this->event['merchant']) ? $event['merchant'] : '');
        // $data_id     = (isset($this->event['data']['id']) ? $event['data']['id'] : '');
        // Cache::lock( 'event-'.$event_name.'-'.$merchant_id.'-'.$data_id,30)->get(function () use($event) {
        // });
        dispatch(new SallaEventProcess($event));
    }
}
