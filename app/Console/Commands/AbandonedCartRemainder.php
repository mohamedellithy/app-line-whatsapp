<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        Http::withOptions([
            'verify' => false
        ])->post('https://webhook-test.com/56f2072b15295bf985c0d0d7a9390e44',[
            'message' => 'heloo mohamed'
        ]);
        return Command::SUCCESS;
    }
}
