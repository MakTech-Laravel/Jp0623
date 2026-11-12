<?php

namespace App\Models;

use Database\Factories\CoffeeServiceRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $business_name
 * @property string $zip_code
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string $contact_name
 * @property string $phone
 * @property string $email
 * @property string|null $notes
 * @property bool $terms_accepted
 * @property string $status
 * @property string $operator_sms_status
 * @property int $operator_sms_sent_count
 * @property int $operator_sms_failed_count
 * @property string|null $operator_sms_provider_status
 * @property string|null $operator_sms_note
 * @property Carbon|null $operator_sms_notified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CoffeeServiceRequest extends Model
{
    /** @use HasFactory<CoffeeServiceRequestFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_REVIEWED,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'business_name',
        'zip_code',
        'address',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'contact_name',
        'phone',
        'email',
        'notes',
        'terms_accepted',
        'status',
        'operator_sms_status',
        'operator_sms_sent_count',
        'operator_sms_failed_count',
        'operator_sms_provider_status',
        'operator_sms_note',
        'operator_sms_notified_at',
    ];

    /**
     * Operator purchases of this lead (pending / paid / failed / …).
     *
     * @return MorphMany<LeadPurchase, $this>
     */
    public function leadPurchases(): MorphMany
    {
        return $this->morphMany(LeadPurchase::class, 'leadable');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'terms_accepted' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'operator_sms_sent_count' => 'integer',
            'operator_sms_failed_count' => 'integer',
            'operator_sms_notified_at' => 'datetime',
        ];
    }
}
