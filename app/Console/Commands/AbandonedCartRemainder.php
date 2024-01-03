<?php

namespace App\Console\Commands;

use App\Models\EventStatus;
use Illuminate\Console\Command;
use App\Services\SallaServices\AppEvents;
use Illuminate\Support\Facades\Http;

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
        EventStatus::where([
            ['type' ,'=', 'abandoned.cart'],
            ['status','=','progress'],
            ['values','!=',null],
            ['required_call','>',1]
        ])->whereColumn('count_of_call','!=','required_call')->orderBy('created_at','asc')->chunk(100,function($events){
            foreach($events as $event):
                Http::WithOptions([
                    'verify' => false
                ])->post('https://webhook-test.com/86389df887aa88fe6ed07ace088fe966',[
                    'cart' => $event
                ]);
                $event_abounded_cart = new AppEvents();
                $event_abounded_cart->data = json_decode($event->values,true);
                $event_abounded_cart->make_event();
            endforeach;
        });

        return Command::SUCCESS;
    }
}
