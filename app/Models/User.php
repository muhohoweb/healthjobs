<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{

    use HasRoles;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'selected_role',
        'licence_number',
        'contacts',
        'description',
        'profession',
        'email_verified_at',
        'avatar'
    ];

    /**
     * Get the user's first name.
     */
    // Option 2: Return empty string when null
    /**
     * Get the user's avatar URL.
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Storage::url($value) : null
        );
    }

    /**
     * Get all job interests for this user
     */
    public function jobInterests()
    {
        return $this->hasMany(JobInterest::class);
    }

    /**
     * Get all jobs this user is interested in
     */
    public function interestedJobs()
    {
        return $this->belongsToMany(HealthJob::class, 'job_interests');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    //

    public function isProfileComplete(): bool
    {
        // basic fields on users table
        if (empty($this->name) || empty($this->email)) {
            return false;
        }

        // if jobseeker, check profile details
        if ($this->hasRole('job-seeker')) {
            return !empty($this->licence_number)
                && !empty($this->licence_number_expiry)
                && !empty($this->licence_status)
                && $this->licence_status === 'active'
                && $this->licence_number_expiry > now(); // Check if license is not expired
        }

        if ($this->hasRole('recruiter')) {
            return !empty($this->licence_number)
                && !empty($this->licence_number_expiry)
                && !empty($this->licence_status)
                && $this->licence_status === 'active'
                && $this->licence_number_expiry > now(); // Check if license is not expired
        }

        // employers only need name + email for now
        return true;
    }

    public function facility():HasOne
    {
        return $this->hasOne(Facility::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function mpesaPayments(): HasMany
    {
        return $this->hasMany(MpesaPayment::class);
    }
}
