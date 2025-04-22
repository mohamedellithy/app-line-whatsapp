<?php

namespace App\Console\Commands;

use Exception;
use App\Models\EventStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\SallaServices\AppEvents;
use App\Services\SallaServices\AbandonedCartReminder;

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
        ])->whereColumn('count_of_call','!=','required_call')->orderBy('created_at','asc')->chunk(200,function($events){
            foreach($events as $app_event):
                try{
                    $bandonCart = new AbandonedCartReminder(json_decode($app_event->values,true));
                    $bandonCart->resolve_event($app_event);
                } catch(Exception $e){
                    \Log::info('Abandoned Cart Reminder: '.$e->getMessage());
                }
            endforeach;
        });

        return Command::SUCCESS;
    }
}
