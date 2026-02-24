<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpoReportRow extends Model
{
    /** @use HasFactory<\Database\Factories\KpoReportRowFactory> */
    use HasFactory;

    protected $fillable = [
        'kpo_report_id',
        'invoice_id',
        'entry_date',
        'entry_description',
        'products_amount',
        'services_amount',
        'activity_amount',
        'row_no',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'products_amount' => 'decimal:2',
        'services_amount' => 'decimal:2',
        'activity_amount' => 'decimal:2',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(KpoReport::class, 'kpo_report_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
