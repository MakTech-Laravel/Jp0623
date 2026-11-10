<?php

namespace App\Models;

use Database\Factories\VendingRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $location_type
 * @property string $business_name
 * @property string $address_or_zip
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int|null $employees_on_site
 * @property int|null $daily_visitors
 * @property string $placement
 * @property string|null $space_notes
 * @property string $food_on_site
 * @property string $comparing_quotes
 * @property list<string> $machine_interests
 * @property string $machine_count
 * @property string $existing_vending
 * @property string|null $switch_reason
 * @property string $contact_name
 * @property string|null $job_title
 * @property string $email
 * @property string $phone
 * @property bool $terms_accepted
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class VendingRequest extends Model
{
    /** @use HasFactory<VendingRequestFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_REVIEWED = 'reviewed';

    public const PLACEMENTS = [
        'Inside',
        'Outside (covered)',
    ];

    public const LOCATION_TYPES = [
        'Business / office',
        'Warehouse / factory',
        'Apartment / hotel',
        'School / college',
        'Medical',
        'Gym',
        'Other',
    ];

    public const MACHINE_INTERESTS = [
        'Snack',
        'Drinks',
        'Combo',
        'Healthy',
        'Vendera cooler',
        'Micro market',
        'Coffee',
    ];

    public const MACHINE_COUNTS = ['1', '2', '3', '4+'];

    public const EXISTING_VENDING = [
        'No',
        'Yes, want to switch',
    ];

    public const YES_NO = ['Yes', 'No'];

    protected $fillable = [
        'location_type',
        'business_name',
        'address_or_zip',
        'address',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'employees_on_site',
        'daily_visitors',
        'placement',
        'space_notes',
        'food_on_site',
        'comparing_quotes',
        'machine_interests',
        'machine_count',
        'existing_vending',
        'switch_reason',
        'contact_name',
        'job_title',
        'email',
        'phone',
        'terms_accepted',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'machine_interests' => 'array',
            'terms_accepted' => 'boolean',
            'employees_on_site' => 'integer',
            'daily_visitors' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }
}
