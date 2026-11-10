<?php

namespace App\Models;

use Database\Factories\OperatorProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'first_name',
    'last_name',
    'company_name',
    'phone',
    'owns_machines',
    'machine_types',
    'machines_needed',
    'primary_city',
    'address',
    'city',
    'state',
    'postal_code',
    'latitude',
    'longitude',
    'terms_accepted_at',
    'email_code_confirmed_at',
    'trial_started_at',
])]
class OperatorProfile extends Model
{
    /** @use HasFactory<OperatorProfileFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'owns_machines' => 'boolean',
            'machine_types' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'terms_accepted_at' => 'datetime',
            'email_code_confirmed_at' => 'datetime',
            'trial_started_at' => 'datetime',
        ];
    }
}
