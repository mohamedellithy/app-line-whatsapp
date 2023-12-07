<?php

namespace App\Console\Commands;

use App\Models\EventStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\SallaServices\AppEvents;

class AbandonedCartRemainder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abandoned:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Http::withOptions([
            'verify' => false
        ])->post('https://webhook-test.com/56f2072b15295bf985c0d0d7a9390e44',[
            'message' => 'heloo mohamed'
        ]);

        DB::table('event_status')->where([
            ['type' ,'=', 'abandoned.cart'],
            ['status','!=','success'],
            ['values','!=',null],
            ['required_call','>',1]
        ])->whereColumn('count_of_call','!=','required_call')->chunck(100,function($events){
            foreach($events as $event):
                $event_abounded_cart = new AppEvents();
                $event_abounded_cart->data = json_decode($event->values,true);
                $event_abounded_cart->make_event();
            endforeach;
        });

        return Command::SUCCESS;
    }
}
