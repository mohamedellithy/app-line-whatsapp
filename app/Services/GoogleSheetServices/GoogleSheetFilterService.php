<?php namespace App\Services\GoogleSheetServices;

use App\Models\GoogleSheetAutoReplay;
use App\Services\GoogleSheetServices\AccountService;
use App\Services\GoogleSheetServices\GoogleSheetOperation;

class GoogleSheetFilterService extends GoogleSheetOperation {
    use AccountService;
    public $booking_sheet_words = [];
    public $phone = null;
    public $booking_appointments = [];
    public $message = null;
    public $values_sheet = null;
    public $google_sheet;
    public function __construct(public $user_id){
        parent::__construct();
        $this->google_sheet = GoogleSheetAutoReplay::where([
            'user_id' => $this->user_id
        ])->first();

        $this->booking_sheet_words  = $this->booking_sheet_words();
        $this->booking_appointments = $this->get_appointments();

        $this->values_sheet = $this->google_sheet?->value ? json_decode($this->google_sheet?->value,true): [];
    }

    public function appointments(){
        if(!isset($this->google_sheet->next_appointment)){
            $this->google_sheet->update([
                'next_appointment'    => 'date',
            ]);
        } elseif($this->google_sheet->next_appointment == 'date'){
            $this->save_data($this->google_sheet->next_appointment,$this->booking_appointments[$this->message][0]);
            $this->google_sheet->update([
                'next_appointment'    => 'day'
            ]);
        }
        elseif($this->google_sheet->next_appointment == 'day'){
            $this->save_data($this->google_sheet->next_appointment,$this->booking_appointments[$this->message][1]);
            $this->google_sheet->update([
                'next_appointment'    => 'times'
            ]);
        }
        elseif($this->google_sheet->next_appointment == 'times'){
            foreach($this->booking_appointments as $key => $booking_times):
                if($booking_times[0] == $this->values_sheet['date']){
                    $this->save_data($this->google_sheet->next_appointment,$booking_times[$this->message]);
                    break;
                }
            endforeach;
            $this->google_sheet->update([
                'next_appointment'    => 'end'
            ]);

            // reback to all section
            $this->next_question();
            $this->send_message($this->google_sheet->current_question);
        }

        $need_message = null;
        if($this->google_sheet->next_appointment == 'date'){
            $need_message = "اختيار  تاريخ الحجز المتوفر لديك \n\n";
            $need_message = "قم بالرد بكتابة رقم التاريخ المحدد \n\n";
            foreach($this->booking_appointments as $key => $booking_appointment):
                $Max_Date = strtotime('+30 days');
                $Min_Date = strtotime("+1 days");

                if(($Max_Date >= strtotime($booking_appointment[0])) && ($Max_Date <= strtotime($booking_appointment[0]))){
                    $need_message .= '#'.$key.' => '.$booking_appointment[0]."\n";
                }

            endforeach;
        } elseif($this->google_sheet->next_appointment == 'day'){
            $need_message = "اختيار يوم الحجز المتوفر لديك \n\n";
            $need_message = "قم بالرد بكتابة رقم اليوم المحدد \n\n";
            foreach($this->booking_appointments as $key => $booking_day):
                if($booking_day[0] == $this->booking_appointments[$this->message][0]){
                    $need_message .= '#'.$key.' => '.$booking_day[1]."\n";
                }
            endforeach;
        } elseif($this->google_sheet->next_appointment == 'times'){
            $need_message = "اختيار الوقت المتوفر لديك \n\n";
            $need_message = "قم بالرد بكتابة رقم الوقت المحدد \n\n";
            foreach($this->booking_appointments as $key => $booking_times):
                if($booking_times[0] == $this->booking_appointments[$this->message][0]){
                    foreach($booking_times as $index => $item){
                        if(!in_array($index,[0,1])){
                            $need_message .= '#'.$index.' => '.$item."\n";
                        }
                    }
                }
            endforeach;
        }

        $this->send_message(urlencode($need_message));
    }

    public function save_data($name,$value){
        $this->values_sheet[$name] = trim($value);
        $this->google_sheet->update([
            'value' => $this->values_sheet
        ]);
    }

    public function handle(){
        if(!$this->google_sheet){
            $this->google_sheet = GoogleSheetAutoReplay::create([
                'user_id' => $this->user_id,
                'phone'   => $this->phone,
                'current_question' => null,
                'next_question' => null
            ]);
        }

        if(isset($this->google_sheet->current_question)){
            if($this->google_sheet->current_question != 'موعد الغسيل'){
                $this->save_data($this->google_sheet->current_question,$this->message);
            }
        }

        if($this->google_sheet->next_question =='end'){
            if($this->google_sheet->current_question != 'end'){
                $this->insert_new_row(
                    $this->google_sheet->id,
                    $this->values_sheet
                );

                $this->google_sheet->update([
                    'current_question' => 'end'
                ]);

                $this->send_message("رقم الحجز الخاص بك ". $this->google_sheet?->id);
            }
            return;
        }

        if(!isset($this->google_sheet->current_question)){
            $this->google_sheet->update([
                'current_question' => $this->booking_sheet_words[0][0],
                'next_question'    => 1,
            ]);
        } elseif(isset($this->google_sheet->current_question)){
            if(($this->google_sheet->current_question != 'موعد الغسيل') || ($this->google_sheet->next_appointment == 'end')){
                $this->next_question();
            }
        }

        if(($this->google_sheet->current_question == 'موعد الغسيل') && ($this->google_sheet->next_appointment != 'end')){
            $this->appointments();
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

    public function reset_booking_info(){
        if($this->message == 'اعادة الحجز'){
            if($this->google_sheet){
                $this->delete_selected_row($this->google_sheet?->id);
                $this->google_sheet->delete();
                $this->google_sheet = null;
            }
        }
    }

    public function send_message($message){
        $this->get_access_token();
        $this->get_instance();
        send_message(
            $this->phone,
            $message,
            $this->instance,
            $this->access_token
        );
    }
}