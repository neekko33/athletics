<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Athlete extends Model
{
    protected $fillable = [
        'klass_id',
        'name',
        'gender',
        'number',
    ];

    public function klass(): BelongsTo
    {
        return $this->belongsTo(Klass::class);
    }

    public function grade()
    {
        return $this->hasOneThrough(Grade::class, Klass::class, 'id', 'id', 'klass_id', 'grade_id');
    }

    public function laneAthletes(): HasMany
    {
        return $this->hasMany(LaneAthlete::class);
    }

    public function lanes(): BelongsToMany
    {
        return $this->belongsToMany(Lane::class, 'lane_athletes')->withPivot('relay_position')->withTimestamps();
    }

    public function athleteCompetitionEvents(): HasMany
    {
        return $this->hasMany(AthleteCompetitionEvent::class);
    }

    public function competitionEvents(): BelongsToMany
    {
        return $this->belongsToMany(CompetitionEvent::class, 'athlete_competition_events')->withTimestamps();
    }

    public function events(): HasManyThrough
    {
        return $this->hasManyThrough(Event::class, AthleteCompetitionEvent::class, 'athlete_id', 'id', 'id', 'event_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->klass->full_name . $this->name;
    }

    public function getEventIdsAttribute(): array
    {
        return $this->competitionEvents->pluck('event_id')->toArray();
    }
}
