<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HealthJob extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get all interests for this job
     */
    public function interests(): HasMany
    {
        return $this->hasMany(JobInterest::class);
    }

    /**
     * Get all users interested in this job
     */
    public function interestedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_interests');
    }

    /**
     * Check if a specific user is interested in this job
     */
    public function isInterestedBy(User $user): bool
    {
        return $this->interests()->where('user_id', $user->id)->exists();
    }

    // In your HealthJob model
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    // App\Models\HealthJob.php
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function jobType(): Attribute
    {
        return Attribute::make(
            get: fn ($value) =>  ($value==='part-time') ? 'Locum' : $value,
        );
    }

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'location',
        'job_type',
        'contract_duration',   // add
        'salary_min',
        'salary_max',
        'experience_level',
        'requirements',
        'qualifications',      // add
        'responsibilities',    // add
        'deadline',            // add
        'is_active',
        'user_id',
        'facility_id',
        'cadre',
    ];

    protected $casts = [
        'requirements'     => 'array',
        'qualifications'   => 'array',
        'responsibilities' => 'array',   // add
        'salary_min'       => 'decimal:2',
        'salary_max'       => 'decimal:2',
        'is_active'        => 'boolean',
        'deadline'         => 'date',    // add

    ];
}
