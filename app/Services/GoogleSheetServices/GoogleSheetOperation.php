<?php namespace App\Services\GoogleSheetServices;

use Illuminate\Support\Facades\Cache;

class GoogleSheetOperation {

    public $client;
    public function __construct(){
        $this->client = new \Google\Client();
        $this->client->setDeveloperKey("AIzaSyAtm5AUR8D0_Zvq5O0eF7WgkMXojeMnYgQ");
        $this->client->setApplicationName('Google Sheets API');
        $this->client->setScopes([\Google\Service\Sheets::SPREADSHEETS]);
        $this->client->setAccessType('offline');
        // credentials.json is the key file we downloaded while setting up our Google Sheets API
        $path = 'whats-line-438413-1fd6b70cfd16.json';
        $this->client->setAuthConfig(app()->basePath('public/'.$path));
    }

    public function get_appointments(){
        $appointments = Cache::remember('appointments',180, function (){
            $service      = new \Google\Service\Sheets($this->client);
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
            $service      = new \Google\Service\Sheets($this->client);
            $ColumnsA     = $service->spreadsheets_values->get("13Jlz0AcBG3DtJcfbFjxmZ9VyXAVw2ekblJRMIi89pIk","pg1!1:1");
            return $ColumnsA->getValues();
        });

        return $booking_sheet_words;
    }

    public function insert_new_row($values_sheet){
        $service      = new \Google\Service\Sheets($this->client);
        $response = $service->spreadsheets_values->get("13Jlz0AcBG3DtJcfbFjxmZ9VyXAVw2ekblJRMIi89pIk",'pg1');
        $values = $response->getValues() ?: [];
        $nextRow = count($values) + 1;
        // Create the row data
        $rowData = [
            [
                $values_sheet['اسم العميل'],
                $values_sheet['رقم الطلب'],
                $values_sheet['لوحة السيارة'],
                $values_sheet['اللوكيشن'],
                $values_sheet['day'].' - '.$values_sheet['date'].' - '.$values_sheet['times'],
                $values_sheet['رقم السيارة']
            ], // Values for each column in the row
        ];
        // Prepare the request to insert the row
        $values_rows = new \Google\Service\Sheets\ValueRange([
            'values' => $rowData,
        ]);
        $options = ['valueInputOption' => 'USER_ENTERED'];
        $response = $service->spreadsheets_values->append('13Jlz0AcBG3DtJcfbFjxmZ9VyXAVw2ekblJRMIi89pIk',"pg1!A$nextRow:F$nextRow",$values_rows,$options);
        // $client   = new \GuzzleHttp\Client();
        // $client->request(
        //     'POST',
        //     'https://tasteless-doctor-84.webhook.cool',
        //     [
        //         'json' => [
        //             'body'  =>  $response
        //         ]
        //     ]
        // );
    }
}