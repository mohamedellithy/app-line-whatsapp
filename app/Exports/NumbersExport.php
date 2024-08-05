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
            $phone    = preg_replace("([a-zA-Z]+)","",$phone);
            $data[] = [
                'id'    => $merchant->id,
                'name'  => $merchant?->user?->fullname ?: $merchant?->user?->username,
                'phone' => preg_replace('[^a-zA-Z]',"",$phone),
                'email' => $merchant?->user?->email,
            ];
        endforeach;
        return $data;
    }
}