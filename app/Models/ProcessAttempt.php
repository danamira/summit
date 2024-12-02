<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProcessAttempt extends Model
{
    protected $fillable = ['asset_id', 'successful', 'error'];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }


    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

}
