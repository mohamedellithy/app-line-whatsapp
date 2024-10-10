<?php namespace App\Services\GoogleSheetServices;

use App\Models\GoogleSheetAutoReplay;

class GoogleSheetFilterService {
    public $booking_sheet_words = [];
    public $phone = null;
    public function __construct(){}
    public function handle(){

        $google_sheet = GoogleSheetAutoReplay::where([
            'user_id' => 1
        ])->first();

        if(!$google_sheet){
            $google_sheet = GoogleSheetAutoReplay::create([
                'user_id' => 1,
                'phone'   => $this->phone,
                'current_question' => null,
                'next_question' => null
            ]);
        }

        if(!isset($google_sheet->current_question)){
            $google_sheet->update([
                'current_question' => $this->booking_sheet_words[0][0],
                'next_question'    => 1,
            ]);
        } elseif(isset($google_sheet->current_question)){
            $next_index = $google_sheet->next_question + 1;
            $check_if_have_question = isset($this->booking_sheet_words[0][$next_index]) ? $next_index: 'end';
            $google_sheet->update([
                'current_question' => $this->booking_sheet_words[0][$google_sheet->next_question],
                'next_question'    => $check_if_have_question,
            ]);
        }

        $this->send_message($google_sheet->current_question);
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