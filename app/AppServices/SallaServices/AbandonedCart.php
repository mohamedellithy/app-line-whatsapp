<?php
namespace App\AppServices\SallaServices;

use App\AppServices\AppSettings\AppEvent;
use App\AppServices\AppSettings\AbandonedCart as  AbandonedCartSettings;
use App\Models\AbandBaskts;
use Log;
class AbandonedCart extends AbandonedCartSettings implements AppEvent{
   public $source = "salla";
}
