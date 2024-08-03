<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\WordPressServices\Subscription;
class WordPressController extends Controller
{
    //

    public function subscribers(Request $request){
        new Subscription($data = $request->all());
    }
}
