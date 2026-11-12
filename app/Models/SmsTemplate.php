<?php

namespace App\Models;

use App\Enums\SmsTemplatePurpose;
use Database\Factories\SmsTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property SmsTemplatePurpose $purpose
 * @property string $body
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'name',
    'purpose',
    'body',
    'is_active',
    'sort_order',
])]
class SmsTemplate extends Model
{
    /** @use HasFactory<SmsTemplateFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => SmsTemplatePurpose::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
