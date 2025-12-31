<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestLogger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logger:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = "Sample cron task executed at " . now()->toDateTimeString();
        $this->info($message);
        \Illuminate\Support\Facades\Log::info($message);
    }
}
