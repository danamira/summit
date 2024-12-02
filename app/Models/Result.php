<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{

    protected $fillable = ['process_attempt_id', 'parsable', 'type', 'movie_info', 'series_info', 'clip_info'];

    public function getTitle(): null|string
    {
        if ($this->type == 'movie') {
            return json_decode($this->movie_info, 1)['movie_name'];
        }
        if ($this->type == 'series') {
            return json_decode($this->series_info, 1)['series_name'];
        }
        if ($this->type == 'clip') {
            return json_decode($this->clip_info, 1)['source'];
        }
        return '-';
    }

    public function processAttempt(): BelongsTo
    {
        return $this->belongsTo(ProcessAttempt::class);
    }

    public function getYear(): null|int|string
    {
        if ($this->type == 'movie') {
            return json_decode($this->movie_info, 1)['movie_year'];
        }
        if ($this->type == 'series') {
            return json_decode($this->series_info, 1)['series_year'];
        }
        return null;
    }


    public function asset()
    {
        return $this->processAttempt->asset;
    }

}
