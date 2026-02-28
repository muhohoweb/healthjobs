<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'is_active',
        'features',
        'sort_order',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
        'features'  => 'array',
    ];

    protected function billingCycleLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->billing_cycle) {
                'weekly'     => 'per week',
                'bi-weekly'  => 'every 2 weeks',
                'monthly'    => 'per month',
                'quarterly'  => 'every 3 months',
                'annually'   => 'per year',
                default      => $this->billing_cycle,
            }
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}