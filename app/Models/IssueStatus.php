<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueStatus extends Model
{
    /** @use HasFactory<\Database\Factories\IssueStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'status_id');
    }
}
