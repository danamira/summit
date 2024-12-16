<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\ImdbMatch;
use App\Models\ImdbMatchAttempt;
use App\Models\ImdbTitle;
use App\Services\IMDb;
use App\Services\IMDbOfficial;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LookUpIMDb implements ShouldQueue
{
    use Queueable;

    private function normalizeImdbTypes(string $imdbType): string
    {
        $imdbType = trim($imdbType);
        if ($imdbType == 'Movie') {
            return 'movie';
        }
        if ($imdbType == 'TV Series' || $imdbType == 'TV Mini Series' || $imdbType == 'TV Episode') {
            return 'series';
        }
        if ($imdbType == 'Short' || $imdbType == 'Video') {
            return 'clip';
        }
        return '-';
    }

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


            $imdbResults = (new IMDbOfficial())->search($query);
            if (!$imdbResults) {
                $attempt->update([
                    'successful' => false,
                    'error' => 'No Matches',
                ]);
                return;
            }

            foreach ($imdbResults as $item) {

                $newRecord = [
                    'title' => $item['titleText']['text'],
                    'imdb_id' => $item['id'],
                    'type' => $item['titleType']['text'],
                    'meta' => json_encode([]),
                    'image_url' => $item['primaryImage'] ? $item['primaryImage']['url'] : null,
                ];


                if (array_key_exists('releaseYear', $item)) {
                    $newRecord['release_year_start'] = $item['releaseYear']['year'];
                    if (array_key_exists('endYear', $item['releaseYear'])) {
                        $newRecord['release_year_end'] = $item['releaseYear']['endYear'];
                    }
                }


                $title = ImdbTitle::create($newRecord);


                // TODO: year match

                $yearMatch = null;

                if ($result->getYear()) {
                    if ($title->release_year_start) {
                        $yearMatch = false;
                        if (strval($title->release_year_start) == trim(strval($result->getYear()))) {
                            $yearMatch = true;
                        } else {
                            dump([
                                'imdb' => $title->release_year_start,
                                'gpt' => $result->getYear()
                            ]);
                        }
                    }
                }

                $typeMatch = null;
                if ($result->type) {
                    if ($result->type == 'clip') {
                        /* Clips cannot make mismatches as they represent a movie or series anyway */
                        continue;
                    }
                    if ($title->type) {
                        $typeMatch = false;
                        if (trim(strval($result->type)) == $this->normalizeImdbTypes($title->type)) {
                            $typeMatch = true;
                        }
                    }
                }


                $match = ImdbMatch::create([
                    'imdb_match_attempt_id' => $attempt->id,
                    'imdb_title_id' => $title->id,
                    'levenshtein' => levenshtein($title->title, $query),
                    'year_match' => $yearMatch,
                    'type_match' => $typeMatch,

                ]);


                $attempt->update(['successful' => true]);

            };


        } catch
        (\Exception $exception) {
            $attempt->update([
                'successful' => false,
                'error' => "FILE:" . $exception->getFile() . "LINE" . $exception->getLine() . "ERROR: " . $exception->getMessage()
            ]);
        }
    }
}
