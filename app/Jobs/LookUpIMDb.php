<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\ImdbMatch;
use App\Models\ImdbMatchAttempt;
use App\Models\ImdbTitle;
use App\Services\IMDb;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LookUpIMDb implements ShouldQueue
{
    use Queueable;

    protected Asset $asset;


    /**
     * Create a new job instance.
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query   = $this->asset->result->getTitle();
        $attempt = ImdbMatchAttempt::create([
            'query' => $query,
            'asset_id' => $this->asset->id,
            'initiated' => true,
        ]);


        try {

            $imdb = new IMDb();


            $responseJson = $imdb->search($query);
            $ok           = $responseJson['Response'] == 'True';
            if (!$ok) {
                $attempt->update([
                    'successful' => false,
                    'error' => $responseJson['Error'],
                ]);
                return;
            }

            foreach ($responseJson['Search'] as $item) {

                $title = ImdbTitle::create([
                    'title' => $item['Title'],
                    'imdb_id' => $item['imdbID'],
                    'type' => $item['Type'],
                    'meta' => json_encode($item),
                ]);

                $match = ImdbMatch::create([
                    'imdb_match_attempt_id' => $attempt->id,
                    'imdb_title_id' => $title->id,
                    'levenshtein'=>levenshtein($item['Title'],$query)
                ]);

                // TODO: year match
                // TODO: type match
                // TODO: levenshtein

            };


        } catch (\Exception $exception) {
            $attempt->update([
                'successful' => false,
                'error' => "FILE:" . $exception->getFile() . "LINE" . $exception->getLine() . "ERROR: " . $exception->getMessage()
            ]);
        }
    }
}
