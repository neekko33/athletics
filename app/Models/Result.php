<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'lane_id',
        'athlete_id',
        'result_value',
        'rank',
        'status',
        'notes',
    ];

    protected $casts = [
        'result_value' => 'decimal:2',
    ];

    public function lane(): BelongsTo
    {
        return $this->belongsTo(Lane::class);
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }
}
