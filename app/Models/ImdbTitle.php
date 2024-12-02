<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImdbTitle extends Model
{
    protected $fillable = ['title', 'year', 'type', 'imdb_id','meta'];
}
