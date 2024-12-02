<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SampleAndParse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run';

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
        \App\Jobs\SampleAndParse::dispatch();
        $this->info('Job dispatched');
    }
}
