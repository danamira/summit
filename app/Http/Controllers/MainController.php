<?php

namespace App\Http\Controllers;

use App\Services\GPT;
use App\Services\IMDb;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    public function imdb($query)
    {

        $imdb         = new IMDb();
        $responseJson = $imdb->search($query);
        $ok           = $responseJson['Response'] == 'True';
        if (!$ok) {
            dd($responseJson);
        }


        dd($responseJson);

        foreach ($responseJson['Search'] as $item) {
            dump($item['Title']);
        };

    }


    public function main()
    {
        $titles = array_map(fn($x) => $x['episode_title'], json_decode(Storage::drive('local')->get('sample1.json'), 1));

        shuffle($titles);

        $titles = array_splice($titles, 0, 100);

        $client = GPT::getClient();

        $okays = [];

        $errors = [];

        foreach ($titles as $title) {
            $result = $client->chat()->create([
                'model' => 'gpt-4o',
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => Storage::drive('local')->get('prompts/parse.txt')],
                    ['role' => 'user', 'content' => $title]
                ],
            ]);

            $finalResult = $result->choices[0]->message->content;
            dump($finalResult);
            try {
                $okays[] = [
                    'original' => $title,
                    'parsed' => json_decode($finalResult, 1)
                ];
            } catch (\Exception $exception) {
                $errors[] = [
                    'original' => $title,
                    'response' => $finalResult,
                    'exception' => $exception->getMessage()
                ];
            }

        }

        $timestamp = now()->format('Y-m-d-h-i');

        Storage::drive('local')->makeDirectory($timestamp);

        Storage::drive('local')->put($timestamp . '/okay.json', json_encode($okays));
        Storage::drive('local')->put($timestamp . '/bad.json', json_encode($errors));


        return dd('ok');
    }


    public function stats()
    {
        $timestamp = "2024-11-26-11-05";
        $okay      = json_decode(Storage::drive('local')->get($timestamp . '/okay.json'), 1);
        $bad       = json_decode(Storage::drive('local')->get($timestamp . '/bad.json'), 1);
        $okay      = collect($okay);

        dd([
            'parsed' => $okay->filter(fn($x) => $x['parsed']['parsable'])->count(),
            'not-parsed' => $okay->filter(fn($x) => !$x['parsed']['parsable'])->count(),
            'errors' => count($bad),
        ]);

    }


}
