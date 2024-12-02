<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\ProcessAttempt;
use App\Models\Result;
use App\Services\GPT;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessTitle implements ShouldQueue
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

        $attempt = ProcessAttempt::create(['asset_id' => $this->asset->id, 'initiated' => true]);

        $client    = GPT::getClient();
        $gptResult = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => Storage::drive('local')->get('prompts/parse.txt')],
                ['role' => 'user', 'content' => $this->asset->title]
            ],
        ]);


        $finalResult = $gptResult->choices[0]->message->content;

        try {

            $parsed = json_decode($finalResult, 1);

            $resultRecords = [
                'process_attempt_id' => $attempt->id,
                'parsable' => $parsed['parsable'],
                'type' => $parsed['type'],
                'move_info' => $parsed['type'] == 'movie' ? json_encode($parsed['movie_info']) : null,
                'series_info' => $parsed['type'] == 'movie' ? json_encode($parsed['series_info']) : null,
                'clip_info' => $parsed['type'] == 'movie' ? json_encode($parsed['clip_info']) : null,
            ];

            $result = new Result($resultRecords);
            $result->save();

            $attempt->update(['successful' => true]);

        } catch (\Exception $exception) {

            $attempt->update([
                'successful' => false,
                'error' => "FILE:" . $exception->getFile() . "LINE" . $exception->getLine() . "ERROR: " . $exception->getMessage()
            ]);

        }


    }
}
