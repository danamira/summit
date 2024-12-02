<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{

    protected $fillable = ['process_attempt_id', 'parsable', 'type', 'movie_info', 'series_info', 'clip_info'];

}
