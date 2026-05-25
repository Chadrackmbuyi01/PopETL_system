<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopulationRecord extends Model
{
    use HasFactory;

    protected $table = 'population_record';

    protected $fillable = [
        'country',
        'year',
        'total_population',
        'population_growth',
        'population_density',
        'male_population',
        'female_population',
        'extracted_at',
    ];
    
    protected $casts = [
        'year' => 'integer',
        'total_population' => 'integer',
        'population_growth' => 'decimal:4',
        'population_density' => 'decimal:4',
        'male_population' => 'integer',
        'female_population' => 'integer',
        'extracted_at' => 'datetime',
    ];

    /**
     * Local scope: Order by most recently extracted first, then latest year
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('extracted_at', 'desc')
                     ->orderBy('year', 'desc');
    }

    /**
     * Scope: order by extracted_at descending.
     */
    public function scopeLatestExtracted(Builder $query): Builder
    {
        return $query->orderBy('extracted_at', 'desc');
    }

    /**
     * Scope: filter by country (case-insensitive).
     */
    public function scopeForCountry(Builder $query, string $country): Builder
    {
        return $query->whereRaw('LOWER(country) = ?', [strtolower($country)]);
    }

    /**
     * Get a distinct list of countries currently in the database.
     */
    public static function getUniqueCountries()
    {
        return self::select('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');
    }
}
