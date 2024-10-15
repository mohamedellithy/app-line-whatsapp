<?php namespace App\Services\GoogleSheetServices;

use App\Models\Team;
use App\Models\Account;

trait AccountService {
    public $merchant_team;
    public function get_access_token(){
        // $this->merchant_team = Team::where([
        //     'owner' => $this->user_id
        // ])->first();

        // $this->access_token = $this->merchant_team?->ids;
    }

    public function get_instance(){
        // $account = Account::where([
        //     'team_id' => $this->merchant_team?->id
        // ])->first();

        // $this->instance = $account->token;
    }


}