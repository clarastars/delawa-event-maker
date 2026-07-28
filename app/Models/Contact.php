<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'email', 'phone', 'phone_normalized', 'activated_at'])]
class Contact extends Model
{
    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
        ];
    }

    public function isActivated(): bool
    {
        return $this->activated_at !== null;
    }

    public function markAsActivated(): bool
    {
        if ($this->isActivated()) {
            return false;
        }

        return $this->forceFill(['activated_at' => now()])->save();
    }

    /**
     * Legacy single-voucher accessor kept for the retired invite flow.
     */
    public function voucher(): HasOne
    {
        return $this->hasOne(Voucher::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('entries')
            ->withTimestamps();
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        $normalized = static::normalizePhone($search);

        return $query->where(function (Builder $query) use ($search, $normalized): void {
            $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");

            if ($normalized !== '') {
                $query->orWhere('phone_normalized', 'like', "%{$normalized}%");
            }
        });
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '966') && strlen($digits) >= 11) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '966'.substr($digits, 1);
        }

        if (str_starts_with($digits, '5') && strlen($digits) === 9) {
            return '966'.$digits;
        }

        return $digits;
    }
}
