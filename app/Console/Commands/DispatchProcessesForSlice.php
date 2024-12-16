<?php

namespace App\Console\Commands;

use App\Jobs\ProcessTitle;
use App\Models\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DispatchProcessesForSlice extends Command
{
    protected static function sliceSize(): int
    {
        return 30;
        return 1000;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:process-slice {sliceNumber}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch processing jobs for a slice of assets stored in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sliceNumber     = intval($this->argument('sliceNumber'));
        $offset          = ($sliceNumber - 1) * self::sliceSize();
        $assetsToProcess = Asset::query()->orderBy('id')->offset($offset)->limit(self::sliceSize())->get();

        foreach ($assetsToProcess as $assets) {
            ProcessTitle::dispatch($assets);
        }

        $count = $assetsToProcess->count();

        $this->info($count . ' ' . Str::plural('asset', $count) . ' dispatched to be processed.');


    }
}
