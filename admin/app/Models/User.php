<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'login_id',
        'email_verified_at',
        'password',
        'must_change_password',
        'role',
        'avatar',
        'google_id',
        'streak',
        'score',
        'custom_daily_target',
        'is_active',
        'last_active_at',
    ];

    /**
     * @var list<string>
     */
    protected $appends = ['is_online'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
        'otp_expires_at',
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
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'streak' => 'integer',
            'score' => 'integer',
            'custom_daily_target' => 'integer',
            'last_active_at' => 'datetime',
        ];
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function activitySessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function dailyProgress(): HasMany
    {
        return $this->hasMany(UserDailyProgress::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    /**
     * Non-admin, active users — the one definition of "counts toward
     * ranking/active-user/notification-audience totals" shared by
     * RankingService, ActiveUsersService, and PushNotificationService so
     * they can never drift apart on who qualifies.
     */
    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('role', '!=', 'admin')->where('is_active', true);
    }

    /**
     * Online means a heartbeat landed within the configured timeout — not
     * merely "has a valid auth token", since persistent login can leave a
     * user authenticated for weeks without the app being open.
     */
    protected function isOnline(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->last_active_at) {
                return false;
            }

            $timeout = config('quiz.online_timeout_seconds');

            return $this->last_active_at->greaterThanOrEqualTo(now()->subSeconds($timeout));
        });
    }
}
