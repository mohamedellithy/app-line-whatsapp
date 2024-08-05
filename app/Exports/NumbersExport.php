<?php
namespace App\Exports;

use App\Models\MerchantCredential;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class NumbersExport implements FromArray , WithHeadingRow
{
    use Exportable;

    public function array() : array
    {
        $merchants = MerchantCredential::with('user')->get();
        $data = [];
        foreach($merchants as $merchant):
            $settings = json_decode($merchant->settings,true,5600);
            $phone    = isset($settings['custom_merchant_phone']) ? $settings['custom_merchant_phone'] : $merchant->phone;
            preg_match_all("(\d+)",$phone,$filter_phones);
            $phones_all = isset($filter_phones[0]) ? ( isset($filter_phones[0][0]) ? $filter_phones[0][0] : null) : null;
            $data[] = [
                'id'    => $merchant->id,
                'name'  => $merchant?->user?->fullname ?: $merchant?->user?->username,
                'phone' => str_replace('"','',$phones_all),
                'email' => $merchant?->user?->email,
            ];
        endforeach;
        dd($data);
        return $data;
    }
}