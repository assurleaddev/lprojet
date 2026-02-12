<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use App\Notifications\AdminResetPasswordNotification;
use App\Concerns\AuthorizationChecker;
use App\Observers\UserObserver;
use Illuminate\Auth\Notifications\ResetPassword as DefaultResetPassword;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Overtrue\LaravelFollow\Traits\Follower;   // can follow others
use Overtrue\LaravelFollow\Traits\Followable; // can be followed
use ChristianKuri\LaravelFavorite\Traits\Favoriteability;
use Digikraaft\ReviewRating\Traits\HasReviewRating;
use Modules\Wallet\Models\Wallet;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use AuthorizationChecker;
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use QueryBuilderTrait;
    use Follower, Followable;
    use Favoriteability;
    use HasReviewRating;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'username',
        'avatar_id',
        'banned_at',
        'phone_country_code',
        'phone_number',
        'phone_verified_at',
        'phone_verification_code',
        'phone_verification_code_expires_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'banned_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'phone_verification_code_expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be appended to the model.
     */
    protected $appends = [
        'avatar_url',
        'full_name',
        'initials',
    ];

    /**
     * The relationships that should be eager loaded.
     */
    protected $with = [
        'avatar',
    ];

    public function actionLogs()
    {
        return $this->hasMany(ActionLog::class, 'action_by');
    }

    /**
     * Get the user's metadata.
     */
    public function userMeta()
    {
        return $this->hasMany(UserMeta::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        // Check if the request is for the admin panel
        if (request()->is('admin/*')) {
            $this->notify(new AdminResetPasswordNotification($token));
        } else {
            $this->notify(new DefaultResetPassword($token));
        }
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param  array|string  $permissions
     */
    public function hasAnyPermission($permissions): bool
    {
        if (empty($permissions)) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the user's avatar media.
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_id', 'id');
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_id && $this->avatar) {
            return asset('storage/media/' . $this->avatar->file_name);
        }

        return $this->getGravatarUrl();
    }

    /**
     * Get the Gravatar URL for the model's email.
     */
    public function getGravatarUrl(int $size = 80): string
    {
        // Use site logo as default avatar
        return asset('images/logo/lara-dashboard.png');
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $name = $this->full_name ?: $this->username;
        $words = explode(' ', (string) $name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function receivedReviews()
    {
        return $this->morphMany(\App\Models\Review::class, 'model');
    }

    /**
     * Check if the user is banned.
     */
    public function isBanned(): bool
    {
        return !is_null($this->banned_at);
    }

    /**
     * Get the users that this user has blocked.
     */
    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocked_users', 'user_id', 'blocked_user_id')->withTimestamps();
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeNotBanned($query)
    {
        return $query->whereNull('banned_at');
    }

    /**
     * Get user meta value.
     */
    public function getMeta($key, $default = null)
    {
        $meta = $this->userMeta->where('meta_key', $key)->first();
        return $meta ? $meta->meta_value : $default;
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the count of unread messages for this user.
     */
    public function unreadMessagesCount()
    {
        return \Modules\Chat\Models\Message::whereNull('read_at')
            ->where('user_id', '!=', $this->id)
            ->whereHas('conversation', function ($query) {
                $query->where('user_one_id', $this->id)
                    ->orWhere('user_two_id', $this->id);
            })
            ->count();
    }

    /**
     * Get chat-related notification types.
     */
    public static function getChatNotificationTypes(): array
    {
        return [
            'new_message',
            'offer_received',
            'offer_accepted',
            'offer_rejected',
            'item_sold',
            'item_shipped',
            'order_update',
            'order_completed'
        ];
    }

    /**
     * Get the count of unread social notifications (likes, follows, etc.) for the bell icon.
     */
    public function unreadSocialNotificationsCount()
    {
        return $this->unreadNotifications()
            ->whereNotIn('data->type', self::getChatNotificationTypes())
            ->count();
    }

    /**
     * Get the count of unread chat/offer notifications for the mail icon.
     */
    public function unreadChatNotificationsCount()
    {
        return $this->unreadNotifications()
            ->whereIn('data->type', self::getChatNotificationTypes())
            ->count();
    }

    /**
     * Get recent social notifications for the bell icon dropdown.
     */
    public function socialNotifications($limit = 5)
    {
        return $this->notifications()
            ->whereNotIn('data->type', self::getChatNotificationTypes())
            ->take($limit)
            ->get();
    }
}
