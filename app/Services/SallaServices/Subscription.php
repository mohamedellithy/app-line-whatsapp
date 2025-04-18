<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\SpPlan;
use App\Models\SpUser;
use App\Models\SpPermession;
use App\Models\SpWhatsAppState;
use App\Models\MerchantCredential;
use Illuminate\Support\Facades\Http;
use App\Services\AppSettings\AppEvent;

class Subscription implements AppEvent{

    public $data;
    public $plans;

    public $sort_plans;
    protected $merchant_team = null;
    public function __construct($data){
        // set data
        $this->data = $data;

        $merchant_info = MerchantCredential::where([
            'app_name'       => 'salla',
            'merchant_id'    => $this->data['merchant']
        ])->first();

        if(!$merchant_info) return;

        // merchant
        $this->merchant_team = Team::with('account')->where([
            'owner' => $merchant_info->user_id
        ])->first();

        // set plans
        $this->plans = [
            'free'      => 34,
            'start'     => 2,
            'Starter'   => 34,
            'starter'   => 34,
            'Launch'    => 2,
            'launch'    => 2,
            'growth'    => 3,
            'Professional'  => 4,
            'professional'  => 4
        ];

        $this->sort_plans = [
            34 => 1,
            2  => 2,
            3  => 3,
            4  => 4
        ];

        // track event by using Log
        $this->set_log();
    }

    public function set_log(){
        // encode log
        //$log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    }

    public function resolve_event(){
        $end_date_full = $this->data['data']['end_date'];
        $end_date      = substr($end_date_full, 0, 10);
        $plan_id       = $this->plans[$this->data['data']['plan_name']] ?: 34;

        $package = SpPlan::findOrFail($plan_id);
        if($package):
            $new_team              = $this->merchant_team;
            $new_team->pid         = $plan_id;
            $new_team->permissions = $package->permissions;
            $new_team->save();

            $upgrade_plan = SpUser::where('id',$new_team->owner)->first();
            $upgrade_plan->plan = $plan_id;
            $upgrade_plan->expiration_date = strtotime($end_date);
            $upgrade_plan->save();

            SpWhatsAppState::where([
                'team_id' => $new_team->id
            ])->update([
                'wa_total_sent_by_month' => 0
            ]);

        endif;

        //$user = SpUser::where('ids',$this->data['merchant'])->first();

        // if($this->sort_plans[$user->plan] < $this->sort_plans[$plan_id] ):
        //     $upgrade_plan = SpUser::where('ids',$this->data['merchant'])->update([
        //         'plan'          => $plan_id,
        //         'expiration_date'=> strtotime($end_date)
        //     ]);
        // endif;

    }
}
