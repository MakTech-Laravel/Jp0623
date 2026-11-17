<?php

namespace App\Models;

use Database\Factories\LegalPageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property Carbon|null $last_updated_on
 * @property list<array{heading: string|null, body: string}> $sections
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class LegalPage extends Model
{
    /** @use HasFactory<LegalPageFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'last_updated_on',
        'sections',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_updated_on' => 'date',
            'sections' => 'array',
        ];
    }
}
