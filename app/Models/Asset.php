<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Asset extends Model
{

    protected $fillable = ['title', 'asset_id', 'asset_type', 'asset_custom_id', 'asset_label'];

    public function processAttempts(): HasMany
    {
        return $this->hasMany(ProcessAttempt::class);
    }

    public function imdbMatchAttempts(): HasMany
    {
        return $this->hasMany(ImdbMatchAttempt::class);
    }

    public function imdbMatches(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(ImdbMatch::class, ImdbMatchAttempt::class);
    }

    public function result(): HasOneThrough
    {
        return $this->hasOneThrough(Result::class, ProcessAttempt::class);
    }


    public function bestImdbTitle()
    {
        /* TODO: Work on this a little */
        /* Take types and years into consideration */
        return $this->imdbMatches()->get()->sortBy('levenshtein')->map(fn($match) => $match->imdbTitle)->first();
    }

    public function bestImdbMatch()
    {
        /* TODO: Work on this a little */
        /* Take types and years into consideration */
        return $this->imdbMatches()->get()->sortBy('levenshtein')->first();
    }


}
