<?php

namespace App\Models;

use Database\Factories\MachineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $machine_category_id
 * @property string $name
 * @property string $slug
 * @property string $kind
 * @property list<string>|null $tags
 * @property string|null $description
 * @property string|null $image
 * @property string|null $capacity
 * @property string|null $dimensions
 * @property string|null $best_for
 * @property string $status
 * @property bool $is_featured
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string|null $image_url
 * @property-read MachineCategory $category
 */
class Machine extends Model
{
    /** @use HasFactory<MachineFactory> */
    use HasFactory;

    protected $fillable = [
        'machine_category_id',
        'name',
        'slug',
        'kind',
        'tags',
        'description',
        'image',
        'capacity',
        'dimensions',
        'best_for',
        'status',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $appends = ['image_url'];

    /**
     * @return BelongsTo<MachineCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MachineCategory::class, 'machine_category_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image === null || $this->image === '') {
            return null;
        }

        if (str_starts_with($this->image, '/') || str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
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
