<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $kind
 * @property list<string>|null $tags
 * @property string|null $description
 * @property string|null $image
 * @property bool $is_featured
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string|null $image_url
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'kind',
        'tags',
        'description',
        'image',
        'is_featured',
        'is_active',
        'sort_order',
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
            'tags' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
