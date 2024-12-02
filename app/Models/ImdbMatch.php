<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImdbMatch extends Model
{
    protected $fillable = ['imdb_title_id', 'year_match', 'type_match', 'levenshtein', 'imdb_match_attempt_id'];

    public function imdbMatchAttempt(): BelongsTo
    {
        return $this->belongsTo(ImdbMatchAttempt::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
