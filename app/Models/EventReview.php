<?php

namespace App\Models;

use Database\Factories\EventReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReview extends Model
{
    /** @use HasFactory<EventReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'contact_id',
        'experience',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
