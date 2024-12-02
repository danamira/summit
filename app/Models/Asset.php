<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Asset extends Model
{

    protected $fillable = ['title'];

    public function processAttempts(): HasMany
    {
        return $this->hasMany(ProcessAttempt::class);
    }


    public function result(): HasOneThrough
    {
        return $this->hasOneThrough(Result::class, ProcessAttempt::class);
    }


}
