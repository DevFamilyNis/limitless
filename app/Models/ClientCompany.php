<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCompany extends Model
{
    protected $fillable = [
        'client_id',
        'pib',
        'mb',
        'bank_account',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
