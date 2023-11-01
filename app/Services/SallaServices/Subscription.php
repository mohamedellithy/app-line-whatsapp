<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\SpPlan;
use App\Models\SpUser;
use App\Models\SpPermession;
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

        // merchant
        $this->merchant_team = Team::with('account')->where([
            'owner' => $merchant_info->user->id
        ])->first();

        // set plans
        $this->plans = [
            'free'      => 34,
            'start'     => 2,
            'growth'    => 3,
            'Professional'  => 4
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
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    }

    public function resolve_event(){
        $end_date_full = $this->data['data']['end_date'];
        $end_date      = substr($end_date_full, 0, 10);
        $plan_id       = $this->plans[$this->data['data']['plan_name']] ?: 34;

        $package = SpPlan::findOrFail($plan_id);
        if($package):
            $new_team              = Team::where('ids',$this->data['merchant'])->first();
            $new_team->pid         = $plan_id;
            $new_team->permissions = $package->permissions;
            $new_team->save();
            // $this->merchant_team->update([
            //     'pid'         => $plan_id,
            //     'permissions' => $package->permissions,
            // ]);
        endif;

        $user = SpUser::where('ids',$this->data['merchant'])->first();

        if($this->sort_plans[$user->plan] < $this->sort_plans[$plan_id] ):
            $upgrade_plan = SpUser::where('ids',$this->data['merchant'])->update([
                'plan'          => $plan_id,
                'expiration_date'=> strtotime($end_date)
            ]);
        endif;
    }
}
