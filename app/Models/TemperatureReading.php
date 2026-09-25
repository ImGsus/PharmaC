<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemperatureReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'recorded_by', 'location', 'temperature', 'minimum_temperature',
        'maximum_temperature', 'source', 'notes', 'recorded_at',
    ];

    protected $casts = ['recorded_at' => 'datetime'];

    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }

    public function getIsOutOfRangeAttribute(): bool
    {
        return ($this->minimum_temperature !== null && $this->temperature < $this->minimum_temperature)
            || ($this->maximum_temperature !== null && $this->temperature > $this->maximum_temperature);
    }
}