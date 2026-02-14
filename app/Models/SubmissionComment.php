<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionComment extends Model
{
    use HasFactory;

    protected $table = 'submission_comments';

    protected $fillable = [
        'submission_id',
        'user_id',
        'body',
        'is_helpful',
        'parent_id', // для ответов на комментарии (опционально)
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Отношение к работе
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Отношение к автору комментария
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Отношение к родительскому комментарию (для ответов)
     */
    public function parent()
    {
        return $this->belongsTo(SubmissionComment::class, 'parent_id');
    }

    /**
     * Отношение к ответам на комментарий
     */
    public function replies()
    {
        return $this->hasMany(SubmissionComment::class, 'parent_id');
    }

    /**
     * Проверка, является ли комментарий ответом
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Получение автора комментария с ролью
     */
    public function getAuthorWithRoleAttribute(): string
    {
        $roleNames = [
            'admin' => 'Администратор',
            'jury' => 'Член жюри',
            'participant' => 'Участник',
        ];

        return $this->user->name . ' (' . ($roleNames[$this->user->role] ?? $this->user->role) . ')';
    }

    /**
     * Получение краткого текста комментария
     */
    public function getExcerptAttribute(int $length = 100): string
    {
        return mb_substr(strip_tags($this->body), 0, $length) . '...';
    }

    /**
     * Скоуп для получения только корневых комментариев (не ответов)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Скоуп для получения полезных комментариев
     */
    public function scopeHelpful($query)
    {
        return $query->where('is_helpful', true);
    }

    /**
     * Скоуп для комментариев определенного пользователя
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Скоуп для комментариев к определенной работе
     */
    public function scopeForSubmission($query, int $submissionId)
    {
        return $query->where('submission_id', $submissionId);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // При создании комментария
        static::creating(function ($comment) {
            // Нельзя отвечать на ответ (ограничение глубины)
            if ($comment->parent_id) {
                $parent = self::find($comment->parent_id);
                if ($parent && $parent->parent_id) {
                    throw new \Exception('Нельзя создавать вложенные ответы глубже 1 уровня');
                }
            }
        });

        // При удалении комментария удаляем все ответы на него
        static::deleting(function ($comment) {
            $comment->replies()->delete();
        });
    }
}
