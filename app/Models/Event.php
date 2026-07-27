<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'banner_path',
    'closed_at',
    'closed_by_user_id',
    'closure_observations',
    'closure_lessons_learned',
    'closure_recommendations',
    'closure_pdf_path',
    'closure_register_path',
])]
class Event extends Model
{
    use HasFactory;

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('closed_at');
    }

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    public function bannerUrl(): ?string
    {
        return $this->banner_path !== null
            ? Storage::disk('public')->url($this->banner_path)
            : null;
    }

    public static function generateUniqueSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(8));
        } while (static::query()->where('slug', $slug)->exists());

        return $slug;
    }
}
