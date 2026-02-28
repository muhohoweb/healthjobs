<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'package_id',
        'merchant_request_id',
        'checkout_request_id',
        'phone_number',
        'amount',
        'account_reference',
        'mpesa_receipt_number',
        'result_code',
        'result_desc',
        'status',
        'callback_data',
        'paid_at',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'callback_data' => 'array',
        'paid_at'       => 'datetime',
        'result_code'   => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' && $this->result_code === 0;
    }
}