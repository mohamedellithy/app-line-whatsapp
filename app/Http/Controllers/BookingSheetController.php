<?php

namespace App\Http\Controllers;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use App\Models\GoogleSheetAutoReplay;
use Google\Service\Sheets\SpreadSheet;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use App\Services\GoogleSheetServices\GoogleSheetFilterService;

class BookingSheetController extends Controller
{
    public function get_appointments(){
        $client = new \Google\Client();
        $client->setDeveloperKey("AIzaSyAtm5AUR8D0_Zvq5O0eF7WgkMXojeMnYgQ");
        $client->addScope(\Google\Service\Drive::DRIVE);
        $service      = new \Google\Service\Sheets($client);
        $ColumnsA     = $service->spreadsheets_values->get("1xnQe0vsH1fKAliiAWJxPou-7NPu26yMTeMxi7Sq1x3Y","pg1!A:A");
        $ColumnsCount = count($ColumnsA->getValues());
        $result       = [];
        for($i = 2;$i <= $ColumnsCount;$i++){
            $ColumnItem = $service->spreadsheets_values->get("1xnQe0vsH1fKAliiAWJxPou-7NPu26yMTeMxi7Sq1x3Y","pg1!".$i.":".$i);
            $result[]   = $ColumnItem->getValues()[0];
        }

        return response()->json([
            'body'    => $result
        ]);
    }

    public function booking_sheet_words(){
        $client = new \Google\Client();
        $client->setDeveloperKey("AIzaSyAtm5AUR8D0_Zvq5O0eF7WgkMXojeMnYgQ");
        $client->addScope(\Google\Service\Drive::DRIVE);
        $service      = new \Google\Service\Sheets($client);
        $ColumnsA     = $service->spreadsheets_values->get("13Jlz0AcBG3DtJcfbFjxmZ9VyXAVw2ekblJRMIi89pIk","pg1!1:1");

       return $ColumnsA->getValues();
    }


    public function auto_replay(Request $request){
        $data = $request->all();
        $google_sheet = GoogleSheetAutoReplay::where([
            'user_id' => 1
        ])->first();

        if($data['data']['event'] == 'messages.upsert'){
            foreach($data['data']['data']['messages'] as $message):
                if($message['key']['fromMe'] == false){
                    $body = isset($message['message']['conversation']) ? $message['message']['conversation'] : $message['message']['extendedTextMessage']['text'];
                    $phone = intval($message['key']['remoteJid']);
                    $googel_sheet = new GoogleSheetFilterService();
                    $googel_sheet->phone = $phone;
                    $googel_sheet->message = $body;
                    $googel_sheet->booking_sheet_words  = $this->booking_sheet_words();
                    $googel_sheet->booking_appointments = $this->get_appointments();
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
