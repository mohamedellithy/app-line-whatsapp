<?php
namespace App\Services\SallaServices;

use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\AbandonedCart as  AbandonedCartSettings;
use App\Models\AbandBaskts;
use Log;
class AbandonedCart extends AbandonedCartSettings implements AppEvent{
   public $source = "salla";
}
