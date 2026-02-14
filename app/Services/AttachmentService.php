<?php

namespace App\Services;

use App\Jobs\ScanAttachmentJob;
use App\Models\Attachment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    private const MAX_FILES = 3;
    private const MAX_SIZE = 10485760; // 10MB
    private const ALLOWED_EXTENSIONS = ['pdf', 'zip', 'png', 'jpg', 'jpeg'];
    private const ALLOWED_MIMES = [
        'application/pdf',
        'application/zip',
        'application/x-zip-compressed',
        'image/png',
        'image/jpeg',
    ];

    public function upload(Submission $submission, User $user, UploadedFile $file): Attachment
    {
        if ($submission->attachments()->count() >= self::MAX_FILES) {
            throw new \Exception('Максимальное количество файлов: ' . self::MAX_FILES);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \Exception('Недопустимый тип файла. Разрешены: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        if ($file->getSize() > self::MAX_SIZE) {
            throw new \Exception('Размер файла превышает 10MB');
        }

        return DB::transaction(function () use ($submission, $user, $file) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $storageKey = 'attachments/' . $submission->id . '/' . $filename;

            Storage::disk('s3')->putFileAs(
                'attachments/' . $submission->id,
                $file,
                $filename
            );

            $attachment = $submission->attachments()->create([
                'user_id' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'storage_key' => $storageKey,
                'status' => Attachment::STATUS_PENDING,
            ]);

            ScanAttachmentJob::dispatch($attachment);
            return $attachment;
        });
    }

    public function markScanned(Attachment $attachment): void
    {
        $attachment->update([
            'status' => Attachment::STATUS_SCANNED,
            'rejection_reason' => null,
        ]);
    }

    public function reject(Attachment $attachment, string $reason): void
    {
        $attachment->update([
            'status' => Attachment::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    public function getSignedUrl(Attachment $attachment): string
    {
        $user = auth()->user();

        if (!$user) {
            throw new \Exception('Не авторизован');
        }

        if ($user->isParticipant() && $attachment->submission->user_id !== $user->id) {
            throw new \Exception('Доступ запрещен');
        }

        return Storage::disk('s3')->temporaryUrl($attachment->storage_key, now()->addMinutes(5));
    }

    public function delete(Attachment $attachment): bool
    {
        return DB::transaction(function () use ($attachment) {
            Storage::disk('s3')->delete($attachment->storage_key);
            return $attachment->delete();
        });
    }
}
