<?php

namespace App\Http\Controllers;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets\SpreadSheet;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
class BookingSheetController extends Controller
{
    public function booking_sheet(){
        $client = new \Google\Client();
        $client->setDeveloperKey("AIzaSyAtm5AUR8D0_Zvq5O0eF7WgkMXojeMnYgQ");
        $client->addScope(\Google\Service\Drive::DRIVE);
        $service = new Google_Service_Sheets($client);
        $result = $service->spreadsheets_values->get("13Jlz0AcBG3DtJcfbFjxmZ9VyXAVw2ekblJRMIi89pIk");
        try {
            $numRows = $result->getValues() != null ? count($result->getValues()) : 0;
            printf("%d rows retrieved.", $numRows);
            return $result;
        }
        catch(\Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }
        var_dump("hi mohamed");
    }

}