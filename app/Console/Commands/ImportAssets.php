<?php

namespace App\Console\Commands;

use App\Jobs\ProcessTitle;
use App\Models\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;

class ImportAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:assets {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import asset titles from a JSON export of the Big Query table';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $clearDatabase = confirm(
            label: 'Do you want to clear the database content first?',
            default: true
        );

        $dispatchProcessJobs = confirm(
                label: "Would you want to process all the asset title right after importing them?",
                default: true,
                hint: 'This would dispatch a job for each asset to be processed in the queue right after being added to the database') == 'yes';

        $parsedJSON = json_decode(Storage::drive('local')->get('/data/' . $this->argument('filename')), 1);

        if ($clearDatabase) {
            DB::table('results')->delete();
            DB::table('process_attempts')->delete();
            DB::table('assets')->delete();
        }

        if (!$parsedJSON) {
            error("⚠️ Problem in parsing file `{$this->argument('filename')}`. Make sure it's placed in storage/private/data.");
            return;
        }


        foreach ($parsedJSON as $asset_item) {

            $title = $asset_item['episode_title'];
            if (!$title) {
                continue;
            }

            $asset = Asset::create(['title' => $title]);

            if ($dispatchProcessJobs) {
                ProcessTitle::dispatch($asset);
            }

        }

    }
}
