<?php
namespace App\Exports;

use App\Models\MerchantCredential;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class NumbersExport implements FromCollection
{
    use Exportable;

    public function collection()
    {
        $merchants = MerchantCredential::with('user')->get();
        $data = [];
        foreach($merchants as $merchant):
            $settings = json_decode($merchant->settings,true,5600);
            $data[] = [
                'id'    => $merchant->id,
                'name'  => $merchant?->user?->fullname ?: $merchant->user->username,
                'phone' => isset($settings['custom_merchant_phone']) ? $settings['custom_merchant_phone'] : $settings->phone,
                'email' => $merchant?->user?->email,
            ];
        endforeach;
        return $data;
    }
}