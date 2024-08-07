<?php

namespace App\Console\Commands;

use App\Models\SallaWebhook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\SallaServices\AppEvents;

class SentSallaWebHooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sent:salla_webhooks';

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
        $events_list = SallaWebhook::limit(100)->get();
        foreach($events_list as $event_item){
            try {
                DB::beginTransaction();
                $event = new AppEvents();
                $handle_event = json_decode($event_item->event,true,2147483);
                if(is_array($handle_event) && (count($handle_event) > 0)){
                    $event->make_event($handle_event);
                }
                $event_item->delete();
                DB::commit();
            } catch(\Exception $e){
                \Log::info($e->getMessage());
                DB::rollBack();
                //$event_item->delete();
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
