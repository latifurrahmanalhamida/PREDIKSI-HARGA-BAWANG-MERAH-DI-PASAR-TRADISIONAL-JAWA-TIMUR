<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PredictionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'region',
        'input_prices',
        'predicted_price',
        'confidence_score',
        'runtime_ms',
        'mape_score',
        'trend_direction',
        'trend_percentage',
        'predicted_for_date'
    ];

    protected $casts = [
        'input_prices' => 'array',
        'predicted_price' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'mape_score' => 'decimal:4',
        'trend_percentage' => 'decimal:2',
        'predicted_for_date' => 'datetime'
    ];

    // Scopes for easy querying
    public function scopeForRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon:: now()->subDays($days));
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    // Helper methods
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->predicted_price, 0, ',', '.');
    }

    public function getTrendIconAttribute()
    {
        switch($this->trend_direction) {
            case 'up':  return '↗️';
            case 'down':  return '↘️';
            default: return '➡️';
        }
    }
}