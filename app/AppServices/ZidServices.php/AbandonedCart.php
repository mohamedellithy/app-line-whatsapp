<?php
namespace App\AppServices\ZidServices;

use App\AppServices\AppSettings\AppEvent;
use App\AppServices\AppSettings\AbandonedCart as  AbandonedCartSettings;
use App\Models\AbandBaskts;
use Log;
class AbandonedCart extends AbandonedCartSettings implements AppEvent{
   public $source = "zid";
}
