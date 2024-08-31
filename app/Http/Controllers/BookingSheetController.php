<?php

namespace App\Http\Controllers;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets\SpreadSheet;
use Illuminate\Http\Request;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;

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
        for($i = 1;$i <= $ColumnsCount;$i++){
            $ColumnItem = $service->spreadsheets_values->get("1xnQe0vsH1fKAliiAWJxPou-7NPu26yMTeMxi7Sq1x3Y","pg1!".$i.":".$i);
            $result[]   = $ColumnItem->getValues()[0];
        }

        return response()->json([
            'body'    => $result
        ]);
    }

    public function booking_sheet($user_id){
        $client = new \Google\Client();
        $client->setDeveloperKey("AIzaSyAtm5AUR8D0_Zvq5O0eF7WgkMXojeMnYgQ");
        $client->addScope(\Google\Service\Drive::DRIVE);
        $service      = new \Google\Service\Sheets($client);
        $ColumnsA     = $service->spreadsheets_values->get("13Jlz0AcBG3DtJcfbFjxmZ9VyXAVw2ekblJRMIi89pIk","pg1!1:1");

        return response()->json([
            'body'    => $ColumnsA->getValues(),
            'user_id' => $user_id
        ]);
    }


    public function auto_replay(Request $request){
        $data = $request->all();
        return response()->json([
            'body'    => $data
        ]);
    }

}
