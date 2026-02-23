<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssuePriority extends Model
{
    /** @use HasFactory<\Database\Factories\IssuePriorityFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'priority_id');
    }
}
