<?php

namespace App\Exceptions;

use Exception;

class MerchantValidateException extends Exception
{
    public $message;
    public $code;
    public function __construct($message,$code = 200)
    {
        $this->message = $message;
        $this->code = $code;
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->message
        ],$this->code);
    }
}