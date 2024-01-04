<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationSubscriber;
use Carbon\Carbon;
class NotificationUsersPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriber:notification';

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
        $today = Carbon::today();
        $users = SpUser::with('merchant_info')->where('expiration_date','>',$today->timestamp)->first();
        //
        Http::withOptions([
            'verify' => false
        ])->post("https://webhook-test.com/90a420a1883f090be6c46d8c807e981c",[
            'b' => $users,
            't' => $today->timestamp
        ]);
        return Command::SUCCESS;
    }
}
