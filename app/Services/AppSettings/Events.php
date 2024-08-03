<?php
namespace App\Services\AppSettings;

use Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
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

    public function get_json_data($webhook_event){
        // $request_data = request()->all();
        // if(count($request_data) == 0){
        //     $request_data =  json_decode(request()->getContent(),true);
        // }
        // return $request_data;
        return $webhook_event;
    }

    public function make_event($webhook_event = []){
        // identity events
        $this->events = $this->events();

        // identity data from request api
        $this->data  =  $this->data ?: $this->get_json_data($webhook_event);

        if(!isset($this->events[$this->data['event']])) return;

        $SelectedEvent = $this->events[$this->data['event']];

        $event_class = '\\App\\Services\\'.$this->app.'Services\\'.$SelectedEvent;

        if(class_exists($event_class)):
            // call events and render it
            $target_event  =  new $event_class($this->data);
            return $target_event->resolve_event();
        endif;
    }
}
