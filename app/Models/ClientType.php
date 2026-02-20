<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'name',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
