<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryType extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
