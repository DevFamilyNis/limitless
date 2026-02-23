<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'status_id');
    }

    public function fromStatusChanges(): HasMany
    {
        return $this->hasMany(InvoiceStatusChange::class, 'from_status_id');
    }

    public function toStatusChanges(): HasMany
    {
        return $this->hasMany(InvoiceStatusChange::class, 'to_status_id');
    }
}
