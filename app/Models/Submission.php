<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'user_id',
        'title',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_NEEDS_FIX = 'needs_fix';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Получить все возможные статусы с их описаниями
     */
    public static function getAllStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_SUBMITTED => 'На проверке',
            self::STATUS_NEEDS_FIX => 'Требуется доработка',
            self::STATUS_ACCEPTED => 'Принято',
            self::STATUS_REJECTED => 'Отклонено',
        ];
    }

    /**
     * Получить цвета для статусов
     */
    public static function getStatusColors(): array
    {
        return [
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_SUBMITTED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_NEEDS_FIX => 'bg-orange-100 text-orange-800',
            self::STATUS_ACCEPTED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
        ];
    }

    /**
     * Получить допустимые переходы статусов
     * Теперь жюри может выбрать любой статус, кроме текущего
     */
    public static function getAllowedStatusTransitions(): array
    {
        return [
            self::STATUS_DRAFT => [
                self::STATUS_SUBMITTED,  // Отправить на проверку
                self::STATUS_NEEDS_FIX,  // Запросить доработку (если жюри вмешалось)
            ],
            self::STATUS_SUBMITTED => [
                self::STATUS_ACCEPTED,    // Принять
                self::STATUS_REJECTED,    // Отклонить
                self::STATUS_NEEDS_FIX,   // Отправить на доработку
            ],
            self::STATUS_NEEDS_FIX => [
                self::STATUS_SUBMITTED,   // Отправить на повторную проверку
                self::STATUS_ACCEPTED,    // Принять (если участник все исправил)
                self::STATUS_REJECTED,    // Отклонить (если не исправил)
            ],
            self::STATUS_ACCEPTED => [
                self::STATUS_NEEDS_FIX,   // Вернуть на доработку (в исключительных случаях)
            ],
            self::STATUS_REJECTED => [
                self::STATUS_NEEDS_FIX,   // Дать шанс на исправление
            ],
        ];
    }

    /**
     * Получить следующий рекомендуемый статус
     */
    public function getNextRecommendedStatus(): ?string
    {
        $map = [
            self::STATUS_DRAFT => self::STATUS_SUBMITTED,
            self::STATUS_SUBMITTED => self::STATUS_ACCEPTED,
            self::STATUS_NEEDS_FIX => self::STATUS_SUBMITTED,
        ];

        return $map[$this->status] ?? null;
    }

    /**
     * Проверить, может ли жюри изменить статус на указанный
     */
    public function canJurySetStatus(string $newStatus): bool
    {
        // Жюри может установить любой статус, кроме черновика
        if ($newStatus === self::STATUS_DRAFT) {
            return false;
        }

        // Проверяем по правилам переходов
        $allowed = self::getAllowedStatusTransitions()[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    /**
     * Проверить, может ли участник изменить статус на указанный
     */
    public function canParticipantSetStatus(string $newStatus): bool
    {
        // Участник может только отправить на проверку из черновика или доработки
        if ($this->status === self::STATUS_DRAFT && $newStatus === self::STATUS_SUBMITTED) {
            return true;
        }

        if ($this->status === self::STATUS_NEEDS_FIX && $newStatus === self::STATUS_SUBMITTED) {
            return true;
        }

        return false;
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(SubmissionComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function hasScannedAttachments(): bool
    {
        return $this->attachments()->where('status', 'scanned')->exists();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_NEEDS_FIX]);
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_REJECTED]);
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function needsFix(): bool
    {
        return $this->status === self::STATUS_NEEDS_FIX;
    }

    /**
     * Check if can upload more files
     */
    public function canUploadMoreFiles(): bool
    {
        return $this->canBeEdited() && $this->attachments()->count() < 3;
    }

    /**
     * Get max files count
     */
    public function getMaxFilesCount(): int
    {
        return 3;
    }
}
