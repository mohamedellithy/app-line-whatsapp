<?php
namespace App\Services\SallaServices;

use App\Services\AppSettings\Events;
use Log;
class AppEvents extends Events{
    public $events = array();
    public $data   = array();
    public function events(){
        return [
          'app.store.authorize'      => 'Authorize',
        //   'app.installed'            => 'Installed',
          'abandoned.cart'           => 'AbandonedCart',
          'app.settings.updated'     => 'SettingsUpdate',
          'app.subscription.started' => 'Subscription',
          'app.subscription.renewed' => 'Subscription',
          'order.created'            => 'Order',
          'order.updated'            => 'Order',
          'customer.otp.request'     => 'OtpRequest',
          'customer.created'         => 'CustomerCreated',
          'manual.review.request'    => 'ManualReviewRequest'
        ];
    }
}

