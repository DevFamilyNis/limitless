<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPerson extends Model
{
    protected $table = 'client_person';

    protected $primaryKey = 'client_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
