<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceStatusChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'from_status_id',
        'to_status_id',
        'changed_at',
        'note',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(InvoiceStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(InvoiceStatus::class, 'to_status_id');
    }
}
