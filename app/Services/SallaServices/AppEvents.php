<?php
namespace App\Services\SallaServices;

use App\Services\AppSettings\Events;
use Log;
class AppEvents extends Events{
    public $events = array();
    public $data   = array();
    public $app    = 'Salla';
    public function events(){
        return [
          'app.store.authorize'      => 'Authorize',
          // 'app.installed'            => 'Authorize',
          'abandoned.cart'           => 'AbandonedCart',
          'abandoned.cart.purchased' => 'AbandonedCartPurchased',
          'app.settings.updated'     => 'SettingsUpdate',
          'app.subscription.started' => 'Subscription',
          'app.subscription.renewed' => 'Subscription',
          'order.created'            => 'Order',
          'order.updated'            => 'Order',
          //'order.status.updated'     => 'Order',
          'customer.otp.request'     => 'OtpRequest',
          'customer.created'         => 'CustomerCreated',
          'customer.login'           => 'CustomerCreated',
          'review.added'             => 'ReviewAdded',
          'invoice.created'          => 'InvoiceCreated'
          //'manual.review.request'    => 'ManualReviewRequest'
        ];
    }
}

