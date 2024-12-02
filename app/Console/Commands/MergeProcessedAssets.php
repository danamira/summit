<?php

namespace App\Console\Commands;

use App\Models\Output;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeProcessedAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:merge-processed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge all the assets into a single table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('outputs')->delete();
        $this->info('🗑️ Table emptied.');

        /* TODO: this might be too much to store in RAM */
        $processedAssets = \App\Models\Asset::whereHas('result', fn($q) => $q->where('parsable', true))->get();

        foreach ($processedAssets as $asset) {
            $result = $asset->result;
            if (!$result->parsable) {
                continue;
            }
            $newRecord = [
                'original' => $asset->title,
                'parsable' => true,
                'type' => $result->type,
            ];

            if ($result->type == 'movie') {
                $movieInfo = json_decode($result->movie_info, 1);
                if ($movieInfo) {
                    $newRecord['movie_name']     = $movieInfo['movie_name'];
                    $newRecord['movie_year']     = $movieInfo['movie_year'];
                    $newRecord['movie_language'] = $movieInfo['movie_language'];
                }
            }

            if ($result->type == 'series') {
                $seriesInfo = json_decode($result->series_info, 1);
                if ($seriesInfo) {
                    $newRecord['series_name']      = $seriesInfo['series_name'];
                    $newRecord['series_year']      = $seriesInfo['series_year'];
                    $newRecord['season_number']    = $seriesInfo['season_number'];
                    $newRecord['episode_number']   = $seriesInfo['episode_number'];
                    $newRecord['episode_name']     = $seriesInfo['episode_name'];
                    $newRecord['episode_language'] = $seriesInfo['episode_language'];
                }
            }

            if ($result->type == 'clip') {
                $clipInfo = json_decode($result->clip_info, 1);
                if ($clipInfo) {
                    $newRecord['clip_language']   = $clipInfo['clip_language'];
                    $newRecord['clip_source']     = $clipInfo['source'];
                    $newRecord['clip_is_trailer'] = $clipInfo['trailer'];
                }
            }


            $imdbTitle = $asset->bestImdbTitle();
            if ($imdbTitle) {
                $newRecord['imdb_id'] = $imdbTitle->imdb_id;
            }

            Output::create($newRecord);
        }

        $this->info('✅ Table filled.');
    }
}
