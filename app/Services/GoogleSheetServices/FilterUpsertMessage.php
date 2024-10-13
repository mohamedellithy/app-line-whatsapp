<?php namespace App\Services\GoogleSheetServices;

class FilterUpsertMessage{
    public static function FormateMessage($message){
        if(isset($message['message']['conversation'])){
            return $message['message']['conversation'];
        } elseif(isset($message['message']['locationMessage'])){
            return self::location_message($message);
        } else {
            return $message['message']['extendedTextMessage']['text'];
        }
    }

    public static function location_message($message){
        $message_text  = null;
        $message_text .= "Lat :  ".$message['message']['locationMessage']['degreesLatitude'];
        $message_text .= " | Long : ".$message['message']['locationMessage']['degreesLongitude'];
        return $message_text;
    }
}