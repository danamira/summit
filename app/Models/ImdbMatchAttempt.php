<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImdbMatchAttempt extends Model
{
    protected $fillable = ['query', 'asset_id', 'initiated', 'successful', 'error'];

    public function ImdbMatches(): HasMany
    {
        return $this->hasMany(ImdbMatch::class);
    }

}
