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
                'episode_title' => $asset->title,
                'asset_id' => $asset->asset_id,
                'asset_type' => $asset->asset_type,
                'asset_custom_id' => $asset->asset_custom_id,
                'asset_label' => $asset->asset_label,
                'gpt_parsable' => true,
                'gpt_type' => $result->type == 'clip' ? (json_decode($result->clip_info, 1)['trailer'] ? 'trailer' : 'clip') : $result->type,
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


            $imdbBestMatch = $asset->bestImdbMatch();


            if ($imdbBestMatch) {

                $bestImdbTitle                         = $imdbBestMatch->imdbTitle;
                $newRecord['imdb_id']                  = $bestImdbTitle->imdb_id;
                $newRecord['imdb_title'] = $bestImdbTitle->title;
                $newRecord['imdb_type'] = $bestImdbTitle->type;
                $newRecord['imdb_title_edit_distance'] = $imdbBestMatch->levenshtein;
                $newRecord['imdb_type_match']          = $bestImdbTitle->type_match;
                $newRecord['imdb_year']= $bestImdbTitle->release_year_start;
                $newRecord['imdb_year_end']= $bestImdbTitle->release_year_end;
                $newRecord['imdb_year_match']          = $imdbBestMatch->year_match;
                $newRecord['imdb_year_provided']          = (boolean)$bestImdbTitle->release_year_start;

            }

            Output::create($newRecord);
        }

        $this->info('✅ Table filled.');
    }
}
