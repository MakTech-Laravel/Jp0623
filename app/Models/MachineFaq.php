<?php

namespace App\Models;

use Database\Factories\MachineFaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MachineFaq extends Model
{
    /** @use HasFactory<MachineFaqFactory> */
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

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
