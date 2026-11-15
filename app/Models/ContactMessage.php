<?php

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $company
 * @property string $email
 * @property string|null $phone
 * @property string $topic
 * @property string $message
 * @property bool $terms_accepted
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_REVIEWED = 'reviewed';

    /**
     * @var list<string>
     */
    public const TOPICS = [
        'Getting vending for my business',
        'Service on an existing machine',
        'Becoming an operator',
        'Something else',
    ];

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'topic',
        'message',
        'terms_accepted',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'terms_accepted' => 'boolean',
        ];
    }
}
