<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\SallaServices\AppEvents;
class NodeJsController extends Controller
{
    public function status_send_message(Request $request){
        $instance_id = $request->input('instance_id');
        $type_erro = $request->input('type_erro');
        send_message_error($type_erro,$instance_id);
    }
}
