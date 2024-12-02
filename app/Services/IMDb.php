<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IMDb
{


    public function search(string $query)
    {
        $response = Http::withHeaders([
            config('imdb.host'),
            'X-RapidAPI-Key' => config('imdb.key'),
        ])->get(config('imdb.url'), [
            's' => $query,
            'r' => 'json',
        ]);
        if (!$response->successful()) {
            return false;
        }
        return $response->json();
    }

    public function fetch($imdb_id): ?array
    {
        $response = Http::withHeaders([
            config('imdb.host'),
            'X-RapidAPI-Key' => config('imdb.key'),
        ])->get(config('imdb.url'), [
            'r' => 'json',
            'i' => $imdb_id,
        ]);

        if ($response->successful()) {
            return [
                //                'dvd' => isset($response->json()['DVD']) ? $response->json()['DVD'] == 'N/A' ? null : date('Y-m-d', strtotime($response->json()['DVD'])) : null,
                'dvd' => $this->sanitizeDate($response->json()['DVD'] ?? null),
                'total_seasons' => $this->sanitizeText($response->json()['totalSeasons'] ?? null),
                'plot' => $this->sanitizeText($response->json()['Plot'] ?? null),
                'type' => $this->sanitizeText($response->json()['Type'] ?? null),
                'year' => $this->sanitizeText($response->json()['Year'] ?? null),
                'genres' => $this->sanitizeArray($response->json()['Genre'] ?? null),
                'rated' => $this->sanitizeText($response->json()['Rated'] ?? null),
                'title' => $this->sanitizeText($response->json()['Title'] ?? null),
                'actors' => $this->sanitizeArray($response->json()['Actors'] ?? null),
                'awards' => $this->sanitizeText($response->json()['Awards'] ?? null),
                'poster' => $this->sanitizeText($response->json()['Poster'] ?? null),
                'writers' => $this->sanitizeArray($response->json()['Writer'] ?? null),
                'imdb_id' => $this->sanitizeText($response->json()['imdbID'] ?? null),
                'country' => $this->sanitizeText($response->json()['Country'] ?? null),
                'ratings' => $this->sanitizeArray($response->json()['Ratings'] ?? null),
                'runtime' => explode(' ', $this->sanitizeText($response->json()['Runtime'] ?? null))[0],
                'website' => $this->sanitizeText($response->json()['Website'] ?? null),
                'directors' => $this->sanitizeArray($response->json()['Director'] ?? null),
                'language' => $this->sanitizeText($response->json()['Language'] ?? null),
                'released_at' => $this->sanitizeDate($response->json()['Released'] ?? null),
                'box_office' => $this->sanitizeText($response->json()['BoxOffice'] ?? null),
                'metascore' => $this->sanitizeText($response->json()['Metascore'] ?? null),
                'imdb_votes' => $this->sanitizeText($response->json()['imdbVotes'] ?? null),
                'production' => $this->sanitizeText($response->json()['Production'] ?? null),
                'imdb_rating' => $this->sanitizeText($response->json()['imdbRating'] ?? null),
                'season_number' => $this->sanitizeText($response->json()['Season'] ?? null),
                'episode_number' => $this->sanitizeText($response->json()['Episode'] ?? null),
            ];
        }

        return null;
    }

    public function sanitizeDate($string = null): ?string
    {
        if (!empty($string)) {
            if ($string == 'N/A') {
                return null;
            } else {
                return date('Y-m-d', strtotime($string));
            }
        } else {
            return null;
        }

    }

    public function sanitizeText($string = null): ?string
    {
        if (!empty($string)) {
            if ($string == 'N/A') {
                return null;
            } else {
                return $string;
            }
        } else {
            return null;
        }
    }

    public function sanitizeArray($string = null): ?array
    {
        if (!empty($string)) {
            if ($string == 'N/A') {
                return null;
            } else {
                if (is_array($string)) {
                    return $string;
                }

                return explode(',', preg_replace('/\s*,\s*/', ',', $string));
            }
        } else {
            return null;
        }
    }


}