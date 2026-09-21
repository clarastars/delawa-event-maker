<?php

namespace App\Models;

use Database\Factories\Sa96RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'phone',
    'phone_hash',
    'birth_year',
    'locale',
    'consent_privacy_notice',
    'consent_campaign',
    'consent_capacity',
    'consent_cross_border',
    'consent_marketing',
    'consent_notice_version',
    'consent_method',
    'consent_snapshot',
    'consented_at',
    'ip_address',
    'user_agent',
    'withdrawn_at',
])]
#[Hidden(['phone', 'ip_address', 'user_agent'])]
class Sa96Registration extends Model
{
    /** @use HasFactory<Sa96RegistrationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'phone' => 'encrypted',
            'ip_address' => 'encrypted',
            'birth_year' => 'integer',
            'consent_privacy_notice' => 'boolean',
            'consent_campaign' => 'boolean',
            'consent_capacity' => 'boolean',
            'consent_cross_border' => 'boolean',
            'consent_marketing' => 'boolean',
            'consent_snapshot' => 'array',
            'consented_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('withdrawn_at');
    }

    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->whereNotNull('withdrawn_at');
    }

    public function isWithdrawn(): bool
    {
        return $this->withdrawn_at !== null;
    }
}
