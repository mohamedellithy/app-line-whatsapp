<?php
namespace App\Services\SallaServices;

use App\Models\Team;
use App\Services\AppSettings\AppEvent;
use App\Models\SpPermession;
use Log;
class Subscription implements AppEvent{

    public $data;
    public $plans;
    public function __construct($data){
        // set data
        $this->data = $data;

        // set plans
        $this->plans = [
            'Free'      => 1,
            'Pro'       => 2,
            'Business'  => 3,
            'Enterprise'=> 4
        ];

        // track event by using Log
        $this->set_log();
    }

    public function set_log(){
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // set log data
        Log::channel('subscriptions_events')->info($log);
    }

    public function resolve_event(){
        $merchant_id   = $this->data->merchant;
        $end_date_full = $this->data->data->end_date;
        $end_date      = substr($end_date_full, 0, 10);
        $plan_id       = $this->plans[$this->data->data->plan_name] ?: 1;

        $package = SpPermession::find($plan_id) ?: null;
        if($package){
            echo ' got package details ------- ';
        }else{
            echo "No permessions found";
        }

        $update_team = Team::where([
            'ids'        => $merchant_id,
        ])->update([
            'pid'         => $plan_id,
            'permissions' => $package->permissions,
        ]);

        $log = $merchant_id.' '.$this->data->event.' '.$plan_id.' '.$end_date_full.PHP_EOL;
        if($update_team){
            Log::channel('subscriptions_success_events')->info($log);
        }else{
            Log::channel('subscriptions_failed_events')->error($log);
        }

        echo SallaUser::upgrade_user_plan($merchant_id,$plan_id,$end_date);
    }
}