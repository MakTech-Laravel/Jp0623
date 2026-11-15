<?php

namespace App\Models;

use Database\Factories\LocationTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $image
 * @property string|null $kind
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $show_on_industries
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string|null $image_url
 */
class LocationType extends Model
{
    /** @use HasFactory<LocationTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'kind',
        'sort_order',
        'is_active',
        'show_on_industries',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image === null || $this->image === '') {
            return null;
        }

        if (str_starts_with($this->image, '/') || str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return '/storage/'.$this->image;
    }

    public function usesStoredUpload(): bool
    {
        if ($this->image === null || $this->image === '') {
            return false;
        }

        return ! str_starts_with($this->image, '/') && ! str_starts_with($this->image, 'http');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'show_on_industries' => 'boolean',
        ];
    }
}
