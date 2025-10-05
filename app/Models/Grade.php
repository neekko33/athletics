<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Grade extends Model
{
    protected $fillable = [
        'competition_id',
        'name',
        'order',
    ];

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function klasses(): HasMany
    {
        return $this->hasMany(Klass::class);
    }

    public function athletes(): HasManyThrough
    {
        return $this->hasManyThrough(Athlete::class, Klass::class);
    }

    public function heats(): HasMany
    {
        return $this->hasMany(Heat::class);
    }
}
