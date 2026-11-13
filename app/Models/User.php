<?php

namespace App\Models;

use App\Enums\GuardEnum;
use App\Enums\RoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'avatar'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    public function guardName(): string
    {
        return GuardEnum::WEB->value;
    }

    /**
     * Whether this account holds the super-admin role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleEnum::SUPER_ADMIN->value);
    }

    /**
     * @return HasOne<OperatorProfile, $this>
     */
    public function operatorProfile(): HasOne
    {
        return $this->hasOne(OperatorProfile::class);
    }

    /**
     * @return HasMany<LeadPurchase, $this>
     */
    public function leadPurchases(): HasMany
    {
        return $this->hasMany(LeadPurchase::class);
    }

    /**
     * @return HasOne<OperatorMembership, $this>
     */
    public function membership(): HasOne
    {
        return $this->hasOne(OperatorMembership::class);
    }

    /**
     * @return HasMany<MembershipPayment, $this>
     */
    public function membershipPayments(): HasMany
    {
        return $this->hasMany(MembershipPayment::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
