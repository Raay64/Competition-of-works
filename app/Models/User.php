<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Роли пользователей
     */
    public const ROLE_PARTICIPANT = 'participant';
    public const ROLE_JURY = 'jury';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_login_at',
        'email_verified_at',
        'notification_settings',
        'is_blocked',
        'blocked_at',
        'block_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
        'notification_settings' => 'array',
    ];

    /**
     * Значения по умолчанию для атрибутов
     */
    protected $attributes = [
        'role' => self::ROLE_PARTICIPANT,
        'notification_settings' => '{
            "email_on_status_change": true,
            "email_on_comments": true,
            "email_on_deadline": true,
            "push_notifications": true,
            "digest_frequency": "daily"
        }',
    ];

    /**
     * Проверка роли администратора
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Проверка роли жюри
     */
    public function isJury(): bool
    {
        return $this->role === self::ROLE_JURY;
    }

    /**
     * Проверка роли участника
     */
    public function isParticipant(): bool
    {
        return $this->role === self::ROLE_PARTICIPANT;
    }

    /**
     * Проверка, имеет ли пользователь определенную роль
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Проверка, имеет ли пользователь одну из указанных ролей
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Проверка, заблокирован ли пользователь
     */
    public function isBlocked(): bool
    {
        return $this->is_blocked || $this->blocked_at !== null;
    }

    /**
     * Блокировка пользователя
     */
    public function block(string $reason = null): bool
    {
        return $this->update([
            'is_blocked' => true,
            'blocked_at' => now(),
            'block_reason' => $reason,
        ]);
    }

    /**
     * Разблокировка пользователя
     */
    public function unblock(): bool
    {
        return $this->update([
            'is_blocked' => false,
            'blocked_at' => null,
            'block_reason' => null,
        ]);
    }

    /**
     * Обновление времени последнего входа
     */
    public function updateLastLogin(): bool
    {
        return $this->update(['last_login_at' => now()]);
    }

    /**
     * Получение настроек уведомлений
     */
    public function getNotificationSettings(): array
    {
        return array_merge([
            'email_on_status_change' => true,
            'email_on_comments' => true,
            'email_on_deadline' => true,
            'push_notifications' => true,
            'digest_frequency' => 'daily',
        ], $this->notification_settings ?? []);
    }

    /**
     * Обновление настроек уведомлений
     */
    public function updateNotificationSettings(array $settings): bool
    {
        $currentSettings = $this->getNotificationSettings();
        $newSettings = array_merge($currentSettings, $settings);

        return $this->update(['notification_settings' => $newSettings]);
    }

    /**
     * Проверка, нужно ли отправлять email при смене статуса
     */
    public function wantsEmailOnStatusChange(): bool
    {
        $settings = $this->getNotificationSettings();
        return $settings['email_on_status_change'] ?? true;
    }

    /**
     * Проверка, нужно ли отправлять email при комментариях
     */
    public function wantsEmailOnComments(): bool
    {
        $settings = $this->getNotificationSettings();
        return $settings['email_on_comments'] ?? true;
    }

    /**
     * Проверка, нужно ли отправлять email о дедлайнах
     */
    public function wantsEmailOnDeadline(): bool
    {
        $settings = $this->getNotificationSettings();
        return $settings['email_on_deadline'] ?? true;
    }

    /**
     * Получение частоты дайджеста
     */
    public function getDigestFrequency(): string
    {
        $settings = $this->getNotificationSettings();
        return $settings['digest_frequency'] ?? 'daily';
    }

    /**
     * Отношение к работам
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Отношение к комментариям
     */
    public function comments()
    {
        return $this->hasMany(SubmissionComment::class);
    }

    /**
     * Отношение к файлам
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Отношение к уведомлениям
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Получение непрочитанных уведомлений
     */
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Получение прочитанных уведомлений
     */
    public function readNotifications()
    {
        return $this->notifications()->whereNotNull('read_at');
    }

    /**
     * Подсчет непрочитанных уведомлений
     */
    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Отметить все уведомления как прочитанные
     */
    public function markAllNotificationsAsRead(): bool
    {
        return $this->unreadNotifications()->update(['read_at' => now()]) > 0;
    }

    /**
     * Очистить все уведомления
     */
    public function clearAllNotifications(): bool
    {
        return $this->notifications()->delete() > 0;
    }

    /**
     * Получение работ, где пользователь является автором
     */
    public function authoredSubmissions()
    {
        return $this->submissions();
    }

    /**
     * Получение конкурсов, созданных пользователем (для админа)
     */
    public function createdContests()
    {
        return $this->hasMany(Contest::class, 'created_by');
    }

    /**
     * Получение полного имени с ролью
     */
    public function getNameWithRoleAttribute(): string
    {
        $roleNames = [
            self::ROLE_ADMIN => 'Администратор',
            self::ROLE_JURY => 'Член жюри',
            self::ROLE_PARTICIPANT => 'Участник',
        ];

        return $this->name . ' (' . ($roleNames[$this->role] ?? $this->role) . ')';
    }

    /**
     * Получение инициалов
     */
    public function getInitialsAttribute(): string
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';

        foreach ($nameParts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }

        return substr($initials, 0, 2);
    }

    /**
     * Получение аватара (цветной круг с инициалами)
     */
    public function getAvatarColorAttribute(): string
    {
        $colors = [
            '#4f46e5', // indigo
            '#10b981', // green
            '#f59e0b', // yellow
            '#ef4444', // red
            '#8b5cf6', // purple
            '#ec4899', // pink
            '#14b8a6', // teal
        ];

        $index = crc32($this->email) % count($colors);
        return $colors[$index];
    }

    /**
     * Проверка, может ли пользователь редактировать работу
     */
    public function canEditSubmission(Submission $submission): bool
    {
        return $this->isAdmin() ||
            ($this->isParticipant() && $submission->user_id === $this->id && $submission->canBeEdited());
    }

    /**
     * Проверка, может ли пользователь просматривать работу
     */
    public function canViewSubmission(Submission $submission): bool
    {
        return $this->isAdmin() ||
            $this->isJury() ||
            ($this->isParticipant() && $submission->user_id === $this->id);
    }

    /**
     * Проверка, может ли пользователь комментировать работу
     */
    public function canCommentOnSubmission(Submission $submission): bool
    {
        return $this->isAdmin() ||
            $this->isJury() ||
            ($this->isParticipant() && $submission->user_id === $this->id);
    }

    /**
     * Проверка, может ли пользователь изменять статус работы
     */
    public function canChangeSubmissionStatus(Submission $submission): bool
    {
        return $this->isAdmin() || $this->isJury();
    }

    /**
     * Проверка, может ли пользователь управлять конкурсами
     */
    public function canManageContests(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Проверка, может ли пользователь управлять пользователями
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Скоуп для активных пользователей
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Скоуп для заблокированных пользователей
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Скоуп для пользователей с определенной ролью
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Скоуп для администраторов
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Скоуп для жюри
     */
    public function scopeJury($query)
    {
        return $query->where('role', self::ROLE_JURY);
    }

    /**
     * Скоуп для участников
     */
    public function scopeParticipants($query)
    {
        return $query->where('role', self::ROLE_PARTICIPANT);
    }

    /**
     * Скоуп для поиска по имени или email
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Скоуп для пользователей, заходивших недавно
     */
    public function scopeRecentlyActive($query, int $days = 7)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // При создании пользователя устанавливаем настройки уведомлений по умолчанию
        static::creating(function ($user) {
            if (empty($user->notification_settings)) {
                $user->notification_settings = [
                    'email_on_status_change' => true,
                    'email_on_comments' => true,
                    'email_on_deadline' => true,
                    'push_notifications' => true,
                    'digest_frequency' => 'daily',
                ];
            }
        });

        // При удалении пользователя удаляем связанные данные
        static::deleting(function ($user) {
            // Удаляем комментарии
            $user->comments()->delete();

            // Удаляем уведомления
            $user->notifications()->delete();

            // Файлы и работы остаются (или можно каскадно удалить)
            // Это зависит от бизнес-логики
        });
    }
}
