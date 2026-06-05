<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadCampaign extends Model
{
    /** @use HasFactory<\Database\Factories\LeadCampaignFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
