<?php

namespace App\Models;

use Database\Factories\CoffeeMachineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $text
 * @property string|null $tag
 * @property string|null $image
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string|null $image_url
 */
class CoffeeMachine extends Model
{
    /** @use HasFactory<CoffeeMachineFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'text',
        'tag',
        'image',
        'sort_order',
        'is_active',
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
        ];
    }
}
