<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStatus extends Model
{
    /** @use HasFactory<\Database\Factories\LeadStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
    ];

    public $timestamps = false;

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(LeadComment::class);
    }
}
