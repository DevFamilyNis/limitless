<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAppLink extends Model
{
    /** @use HasFactory<\Database\Factories\ClientAppLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'label',
        'url',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
