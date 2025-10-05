<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'event_type',
        'gender',
        'max_participants',
        'avg_time',
    ];

    public function competitionEvents(): HasMany
    {
        return $this->hasMany(CompetitionEvent::class);
    }

    public function isTrackEvent(): bool
    {
        return $this->event_type === 'track';
    }

    public function isFieldEvent(): bool
    {
        return $this->event_type === 'field';
    }

    public function isRelay(): bool
    {
        return str_contains($this->name, '接力') || str_contains($this->name, '4*100');
    }
}
