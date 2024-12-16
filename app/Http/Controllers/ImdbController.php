<?php

namespace App\Http\Controllers;

use App\Services\IMDbOfficial;
use Aws\Sdk;
use Illuminate\Support\Facades\Storage;


class ImdbController extends Controller
{
    public function search($searchTerm)
    {

//        return dd((new IMDbOfficial())->search($searchTerm));

        $sdk    = new Sdk(
            ['version' => 'latest',
                'region' => config('aws.default_region'),
                'credentials' => [
                    'key' => config('aws.access_key_id'),
                    'secret' => config('aws.secret_access_key'),
                ],
            ]
        );
        $client = $sdk->createDataExchange(['region' => config('aws.default_region')]);


        $query = Storage::drive('local')->get('queries/retrieve_episode.gql');


        $credentials = [
            'Method' => 'POST',
            'Path' => 'https://api-fulfill.dataexchange.us-east-1.amazonaws.com/v1',
            'RequestHeaders' => ['x-api-key' => config('aws.imdb_data_exchange_api_id')],
            'AssetId' => config('aws.imdb_data_exchange_asset_id'),
            'DataSetId' => config('aws.imdb_data_exchange_dataset_id'),
            'RevisionId' => config('aws.imdb_data_exchange_revision_id'),
            'Body' => json_encode(['query' => $query, 'variables' => [
                'id' => 'tt0111161',
            ]
            ]),
        ];


        $result = $client->sendApiAsset($credentials);
        $body   = json_decode($result);
        dd($body);

        $result = collect($body['data']['mainSearch']['edges']);

        $result = $result->map(fn($edge) => $edge['node']['entity']);

        return $result;

    }
}
