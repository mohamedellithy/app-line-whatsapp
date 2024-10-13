<?php namespace App\Services\GoogleSheetServices;

use App\Models\Team;
use App\Models\Account;

trait AccountService {
    public $merchant_team;
    public $access_token;
    public $instance;
    public $user_id;
    public function get_access_token(){
        $this->merchant_team = Team::with('account')->where([
            'owner' => $this->user_id
        ])->first();

        $this->access_token = $this->merchant_team?->ids;
    }

    public function get_instance(){
        $account = Account::where([
            'team_id' => $this->merchant_team?->ids
        ])->first();

        $this->instance = $account->token;
    }


}