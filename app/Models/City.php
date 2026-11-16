<?php

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $city
 * @property string $state
 * @property string|null $postal_code
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    protected $fillable = [
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'sort_order',
        'is_active',
    ];

    /**
     * States are always stored as upper-case 2-letter codes.
     *
     * @return Attribute<string, string>
     */
    protected function state(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => strtoupper(trim($value)),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
