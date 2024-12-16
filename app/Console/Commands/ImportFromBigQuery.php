<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Console\Command;

class ImportFromBigQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:bigQuery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import assets from the corresponding Big Query dataset';

    private static function stepSize(): int
    {
        return 5000;
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {

        $client = new BigQueryClient();

        $dataset = $client->dataset(env('BIGQUERY_DATASET'));
        $table   = $dataset->table(env('BIGQUERY_TABLE'));
        $target  = $dataset . '.' . $table;

        $step = intval($this->argument('step'));

        $offset = ($step - 1) * self::stepSize();

        $limit = self::stepSize();


        $query = $client->query('SELECT *  FROM `lasso-group.summit_' . $target . '` LIMIT ' . $limit . ' OFFSET ' . $offset);

        $results = $client->runQuery($query);

        foreach ($results as $item) {
            Asset::create([
                'title' => $item['episode_title'],
                'asset_id' => $item['asset_id'],
                'asset_type' => $item['asset_type'],
                'asset_label' => $item['asset_label'],
            ]);
        }


    }
}
