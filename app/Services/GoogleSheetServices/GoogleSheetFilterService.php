<?php namespace App\Services\GoogleSheetServices;

use App\Models\GoogleSheetAutoReplay;

class GoogleSheetFilterService {
    public $booking_sheet_words = [];
    public $phone = null;
    public $booking_appointments = [];

    public $message = null;

    public $google_sheet;
    public function __construct(){
        $this->google_sheet = GoogleSheetAutoReplay::where([
            'user_id' => 1
        ])->first();
    }

    public function appointments(){
        if(isset($this->google_sheet->next_appointment)){
            $this->google_sheet->update([
                'next_appointment'    => 'date',
            ]);
        } elseif(isset($this->google_sheet->next_appointment)){
            $this->google_sheet->update([
                'next_appointment'    => 'day'
            ]);
        }

        $need_message = null;
        if($this->google_sheet->next_appointment == 'date'){
            $need_message = "اختيار  تاريخ الحجز المتوفر لديك \n\n";
            $need_message = "قم بالرد بكتابة رقم التاريخ المحدد \n\n";
            foreach($this->booking_appointments as $key => $booking_appointment):
                $need_message .= '#'.$key.' => '.$booking_appointment[0]."\n";
            endforeach;
        } elseif($this->google_sheet->next_appointment == 'day'){
            $need_message = "اختيار يوم الحجز المتوفر لديك \n\n";
            $need_message = "قم بالرد بكتابة رقم اليوم المحدد \n\n";
            foreach($this->booking_appointments as $key => $booking_appointment):
                if($booking_appointment[0] == $this->booking_appointments[$this->message][0]){
                    $need_message .= '#'.$key.' => '.$booking_appointment[1]."\n";
                }
            endforeach;
        }

        $this->send_message(urlencode($need_message));
    }


    public function save_data($name,$value){
        $values_sheet = $this->google_sheet?->value ?: [];
        $values_sheet[$name] = $value;
        $this->google_sheet->update([
            'value' => $values_sheet
        ]);
    }
    public function handle(){
        if(!$this->google_sheet){
            $this->google_sheet = GoogleSheetAutoReplay::create([
                'user_id' => 1,
                'phone'   => $this->phone,
                'current_question' => null,
                'next_question' => null
            ]);
        }

        if(!isset($this->google_sheet->current_question)){
            $this->google_sheet->update([
                'current_question' => $this->booking_sheet_words[0][0],
                'next_question'    => 1,
            ]);
        } elseif(isset($this->google_sheet->current_question)){
            if($this->google_sheet->current_question != 'موعد الغسيل'){
                $this->next_question();
            }
        }

        if($this->google_sheet->current_question == 'موعد الغسيل'){
            $this->appointments();
            if($this->message == 'تم'){
                $this->next_question();
            }
        } else {
            $this->send_message($this->google_sheet->current_question);
        }
    }
    
    public function next_question(){
        $next_index = $this->google_sheet->next_question + 1;
        $check_if_have_question = isset($this->booking_sheet_words[0][$next_index]) ? $next_index: 'end';
        $this->google_sheet->update([
            'current_question' => $this->booking_sheet_words[0][$this->google_sheet->next_question],
            'next_question'    => $check_if_have_question,
        ]);
    }

    public function send_message($message){
        send_message(
            $this->phone,
            $message,
            "6706972B65F4A",
            "2032449688RtpEd"
        );
    }
}