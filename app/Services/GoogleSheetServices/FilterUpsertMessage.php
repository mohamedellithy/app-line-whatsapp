<?php namespace App\Services\GoogleSheetServices;

class FilterUpsertMessage{
    public function __construct(public $message){
        if(isset($this->message['message']['conversation'])){
            return $this->message['message']['conversation'];
        } else {
            return $this->message['message']['extendedTextMessage']['text'];
        }
    }
}