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
        $result = $this->asset->result;
        $query  = $result->getTitle();

        if (!$result->parsable) {
            return;
        }
        if (!in_array($result->type, ['movie', 'series', 'clip']) || !$query) {
            return;
        }


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


                // TODO: year match

                $yearMatch = null;

                if ($result->getYear()) {
                    if (array_key_exists('Year', $item)) {
                        $yearMatch = false;
                        if (trim(strval($result->getYear())) == trim(strval($item['Year']))) {
                            $yearMatch = true;
                        }
                    }
                }

                $typeMatch = null;
                if ($result->type) {
                    if (array_key_exists('Type', $item)) {
                        $typeMatch = false;
                        if (trim(strval($result->type)) == trim(strval($item['Type']))) {
                            $typeMatch = true;
                        }
                    }
                }


                $match = ImdbMatch::create([
                    'imdb_match_attempt_id' => $attempt->id,
                    'imdb_title_id' => $title->id,
                    'levenshtein' => levenshtein($item['Title'], $query),
                    'year_match' => $yearMatch,
                    'type_match' => $typeMatch,

                ]);


                $attempt->update(['successful' => true]);

            };


        } catch (\Exception $exception) {
            $attempt->update([
                'successful' => false,
                'error' => "FILE:" . $exception->getFile() . "LINE" . $exception->getLine() . "ERROR: " . $exception->getMessage()
            ]);
        }
    }
}
