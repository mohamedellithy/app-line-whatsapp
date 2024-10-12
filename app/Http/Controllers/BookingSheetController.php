<?php

namespace App\Http\Controllers;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use App\Models\GoogleSheetAutoReplay;
use Illuminate\Support\Facades\Cache;
use Google\Service\Sheets\SpreadSheet;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use App\Services\GoogleSheetServices\GoogleSheetFilterService;

class BookingSheetController extends Controller
{

    public function auto_replay(Request $request){
        $data = $request->all();
        if($data['data']['event'] == 'messages.upsert'){
            foreach($data['data']['data']['messages'] as $message):
                if($message['key']['fromMe'] == false){
                    $body  = isset($message['message']['conversation']) ? $message['message']['conversation'] : $message['message']['extendedTextMessage']['text'];
                    $phone = intval($message['key']['remoteJid']);
                    $googel_sheet = new GoogleSheetFilterService();
                    $googel_sheet->phone   = $phone;
                    $googel_sheet->message = $body;
                    // incase bookings info reset
                    $googel_sheet->reset_booking_info();
                    $googel_sheet->handle();
                    
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
                }
            endforeach;
        }
        return response()->json([
            'body'    => $data
        ]);
    }

}
