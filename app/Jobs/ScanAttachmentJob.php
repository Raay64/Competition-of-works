<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ScanAttachmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Attachment $attachment;
    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Execute the job.
     */
    public function handle(AttachmentService $attachmentService): void
    {
        try {
            Log::info('Starting attachment scan', [
                'attachment_id' => $this->attachment->id,
                'file' => $this->attachment->original_name
            ]);

            // Проверяем существование файла в S3
            if (!Storage::disk('s3')->exists($this->attachment->storage_key)) {
                throw new \Exception('Файл не найден в хранилище');
            }

            // Получаем метаданные файла
            $fileSize = Storage::disk('s3')->size($this->attachment->storage_key);
            $fileMime = Storage::disk('s3')->mimeType($this->attachment->storage_key);

            // Проверка размера файла
            if ($fileSize > 10485760) { // 10MB
                throw new \Exception('Размер файла превышает 10MB');
            }

            // Проверка MIME-типа
            $allowedMimes = [
                'application/pdf',
                'application/zip',
                'application/x-zip-compressed',
                'image/png',
                'image/jpeg',
                'image/jpg'
            ];

            if (!in_array($fileMime, $allowedMimes)) {
                throw new \Exception('Недопустимый тип файла: ' . $fileMime);
            }

            // Проверка имени файла на вредоносные символы
            if (preg_match('/[^\w\-\.\s\(\)\[\]]/u', $this->attachment->original_name)) {
                throw new \Exception('Имя файла содержит недопустимые символы');
            }

            // Проверка расширения файла
            $extension = pathinfo($this->attachment->original_name, PATHINFO_EXTENSION);
            $allowedExtensions = ['pdf', 'zip', 'png', 'jpg', 'jpeg'];

            if (!in_array(strtolower($extension), $allowedExtensions)) {
                throw new \Exception('Недопустимое расширение файла: ' . $extension);
            }

            // Для изображений можно добавить дополнительную проверку
            if (strpos($fileMime, 'image/') === 0) {
                // Проверка на поврежденное изображение
                $imageContent = Storage::disk('s3')->get($this->attachment->storage_key);
                if (!@imagecreatefromstring($imageContent)) {
                    throw new \Exception('Файл изображения поврежден');
                }
            }

            // Для ZIP-архивов проверяем целостность
            if (in_array($fileMime, ['application/zip', 'application/x-zip-compressed'])) {
                // Здесь можно добавить проверку ZIP архива
                // Например, попытаться открыть архив
            }

            // Все проверки пройдены
            $attachmentService->markScanned($this->attachment);

            Log::info('Attachment scanned successfully', [
                'attachment_id' => $this->attachment->id,
                'status' => 'scanned'
            ]);

        } catch (\Exception $e) {
            // Файл не прошел проверку
            $attachmentService->reject($this->attachment, $e->getMessage());

            Log::error('Attachment scan failed', [
                'attachment_id' => $this->attachment->id,
                'error' => $e->getMessage()
            ]);

            // Можно добавить уведомление для администратора
            if ($this->attempts() >= $this->tries) {
                Log::critical('Attachment scan failed after all attempts', [
                    'attachment_id' => $this->attachment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $e): void
    {
        Log::error('ScanAttachmentJob failed permanently', [
            'attachment_id' => $this->attachment->id,
            'error' => $e->getMessage()
        ]);

        // Отмечаем файл как отклоненный в случае критической ошибки
        $this->attachment->update([
            'status' => Attachment::STATUS_REJECTED,
            'rejection_reason' => 'Критическая ошибка при проверке: ' . $e->getMessage()
        ]);
    }
}
