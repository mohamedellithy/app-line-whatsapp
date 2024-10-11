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
        $need_message = "choice Number Of Date \n";
        foreach($this->booking_appointments as $key => $booking_appointment):
            $need_message .= '#'.$key.' => '.$booking_appointment[0]."\n";
        endforeach;

        $this->send_message($need_message);
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
            if($this->booking_sheet_words[0][$this->google_sheet->next_question] != 'موعد الغسيل'){
                $this->next_question();
            }
        }

        $this->send_message($this->google_sheet->current_question);
        if($this->booking_sheet_words[0][$this->google_sheet->next_question] == 'موعد الغسيل'){
            $this->appointments();
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