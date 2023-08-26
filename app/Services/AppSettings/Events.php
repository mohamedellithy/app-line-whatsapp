<?php
namespace App\Services\AppSettings;

use Log;
use Illuminate\Support\Facades\Http;

abstract class Events
{
    public $events = array();
    public $data   = array();
    public $app    = '';
    public function events(){
        return [
          'app.store.authorize'      => 'Authorize',
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

    public function get_json_data(){
        $request_data = request()->all();
        return $request_data;
    }

    public function make_event(){
        // identity events
        $this->events = $this->events();

        // identity data from request api
        $this->data  =  $this->get_json_data();

        if(!isset($this->events[$this->data['event']])) return;

        $SelectedEvent = $this->events[$this->data['event']];

        $event_class = '\\App\\Services\\'.$this->app.'Services\\'.$SelectedEvent;

        Http::post('https://webhook.site/19694e58-fa42-41d5-a247-2187b0718cf7',$this->data);

        if(class_exists($event_class)):
            // call events and render it
            $target_event  =  new $event_class($this->data);
            return $target_event->resolve_event();
        endif;
    }
}
