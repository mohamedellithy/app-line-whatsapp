<?php namespace App\Services\GoogleSheetServices;

use Illuminate\Support\Facades\Cache;

class GoogleSheetOperation {
    public function get_appointments(){
        $appointments = Cache::remember('appointments',180, function () {
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

            return $result;
        });

        return $appointments;
    }

    public function booking_sheet_words(){
        $booking_sheet_words = Cache::remember('sheet_words',180, function () {
            $client = new \Google\Client();
            $client->setDeveloperKey("AIzaSyAtm5AUR8D0_Zvq5O0eF7WgkMXojeMnYgQ");
            $client->addScope(\Google\Service\Drive::DRIVE);
            $service      = new \Google\Service\Sheets($client);
            $ColumnsA     = $service->spreadsheets_values->get("13Jlz0AcBG3DtJcfbFjxmZ9VyXAVw2ekblJRMIi89pIk","pg1!1:1");
            return $ColumnsA->getValues();
        });

        return $booking_sheet_words;
    }

    public function insert_new_row(){
        $client = new \Google\Client();
        $client->setDeveloperKey("AIzaSyAtm5AUR8D0_Zvq5O0eF7WgkMXojeMnYgQ");
        $client->setApplicationName('Google Sheets API');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        // credentials.json is the key file we downloaded while setting up our Google Sheets API
        $path = 'whats-line-438413-1fd6b70cfd16.json';
        $client->setAuthConfig(app()->basePath('public/'.$path));
        $service      = new \Google\Service\Sheets($client);
        // Get the current values to determine the next available row
        // Create the row data
        $rowData = [
            ['Value1', 'Value2', 'Value3', 'Value4', 'Value5', 'Value6'], // Values for each column in the row
        ];
        // Prepare the request to insert the row
        $values = new \Google\Service\Sheets\ValueRange([
            'values' => $rowData,
        ]);
        $options = ['valueInputOption' => 'USER_ENTERED'];
        $response = $service->spreadsheets_values->append('13Jlz0AcBG3DtJcfbFjxmZ9VyXAVw2ekblJRMIi89pIk','pg1',$values,$options);
    }
}