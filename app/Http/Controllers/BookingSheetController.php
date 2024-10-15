<?php

namespace App\Http\Controllers;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use App\Models\GoogleSheetAutoReplay;
use Illuminate\Support\Facades\Cache;
use Google\Service\Sheets\SpreadSheet;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use App\Services\GoogleSheetServices\FilterUpsertMessage;
use App\Services\GoogleSheetServices\GoogleSheetFilterService;

class BookingSheetController extends Controller
{

    public function auto_replay(Request $request,$user_id,$instance_id,$access_token){
        $data = $request->all();
        \Log::info('hi');
        if($data['data']['event'] == 'messages.upsert'){
            foreach($data['data']['data']['messages'] as $message):
                if($message['key']['fromMe'] == false){
                    $body  = FilterUpsertMessage::FormateMessage($message);
                    $phone = intval($message['key']['remoteJid']);
                    $googel_sheet = new GoogleSheetFilterService($user_id,$instance_id,$access_token);
                    $googel_sheet->phone   = $phone;
                    $googel_sheet->message = $body;
                    // incase bookings info reset
                    $googel_sheet->reset_booking_info();
                    // start booking
                    $googel_sheet->handle();
                }
            endforeach;
        }
        return response()->json([
            'body'    => $data
        ]);
    }

}






















// $client   = new \GuzzleHttp\Client();
// $client->request(
//     'POST',
//     'https://tasteless-doctor-84.webhook.cool',
//     [
//         'json' => [
//             'body'  =>  $body,
//             'get_appointments'     => $this->get_appointments(),
//             'booking_sheet_words'  => $this->booking_sheet_words(),
//             'phone'                => $phone
//         ]
//     ]
// );
