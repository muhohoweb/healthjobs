<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'status',
        'starts_at',
        'expires_at',
        'mpesa_reference',
        'amount_paid',
    ];

    protected $casts = [
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MpesaPayment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at?->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() || $this->status === 'expired';
    }

    // Calculate expiry based on billing cycle
    public static function calculateExpiry(string $billingCycle): Carbon
    {
        return match($billingCycle) {
            'weekly'    => now()->addWeek(),
            'bi-weekly' => now()->addWeeks(2),
            'monthly'   => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'annually'  => now()->addYear(),
            default     => now()->addMonth(),
        };
    }
}