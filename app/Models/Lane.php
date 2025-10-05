<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lane extends Model
{
    protected $fillable = [
        'heat_id',
        'lane_number',
        'position',
    ];

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('lane_number', 'asc');
        });
    }

    public function heat(): BelongsTo
    {
        return $this->belongsTo(Heat::class);
    }

    public function laneAthletes(): HasMany
    {
        return $this->hasMany(LaneAthlete::class);
    }

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(Athlete::class, 'lane_athletes')->withPivot('relay_position')->withTimestamps();
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function isFieldEvent(): bool
    {
        return $this->heat->competitionEvent->event->event_type === 'field';
    }

    public function isRelay(): bool
    {
        return $this->heat->competitionEvent->event->isRelay();
    }

    public function isValidRelayTeam(): bool
    {
        if (!$this->isRelay()) {
            return true;
        }
        return $this->laneAthletes()->count() === 4;
    }
}
