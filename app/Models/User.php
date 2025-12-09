<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int $role_id
 *
 * @method bool isBannedFromForum()
 * @method bool canModerateForum()
 * @method bool isAdmin()
 * @method bool isModerator()
 * @method bool isRegularUser()
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];
    // app/Models/User.php
    protected $casts = [
        'role_id' => 'integer',
        'is_admin' => 'boolean',
        'is_moderator' => 'boolean',
    ];


    protected static function boot()
    {
        parent::boot();
        static::created(function ($user) {
            $user->email_verified_at = now();
            $user->save();
        });
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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

    public function forumMessages(): HasMany
    {
        return $this->hasMany(ForumMessage::class);
    }

    public function forumBans(): HasMany
    {
        return $this->hasMany(ForumBan::class);
    }

    public function activeBan(): HasOne
    {
        return $this->hasOne(ForumBan::class)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function isBannedFromForum(): bool
    {
        return $this->activeBan()->exists();
    }

    public function canModerateForum(): bool
    {
        return in_array($this->role_id, [1, 3]); // Admin atau Moderator
    }

    public function isAdmin(): bool
    {
        return $this->role_id === 1;
    }

    public function isModerator(): bool
    {
        return $this->role_id === 3;
    }

    public function isRegularUser(): bool
    {
        return $this->role_id === 2;
    }
}
