<?php

namespace App\Jobs;

use App\Models\Submission;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSubmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Submission $submission;
    public $timeout = 120;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing submission', [
                'submission_id' => $this->submission->id,
                'title' => $this->submission->title
            ]);

            // Проверяем все файлы работы
            $attachments = $this->submission->attachments;
            $scannedCount = $attachments->where('status', 'scanned')->count();
            $pendingCount = $attachments->where('status', 'pending')->count();
            $rejectedCount = $attachments->where('status', 'rejected')->count();

            // Если есть отклоненные файлы и работа в статусе draft/submitted
            if ($rejectedCount > 0 && in_array($this->submission->status, ['draft', 'submitted'])) {
                $this->handleRejectedAttachments();
            }

            // Если все файлы проверены и работа в статусе submitted
            if ($pendingCount === 0 && $this->submission->status === 'submitted') {
                $this->notifyJury();
            }

            Log::info('Submission processed', [
                'submission_id' => $this->submission->id,
                'scanned' => $scannedCount,
                'pending' => $pendingCount,
                'rejected' => $rejectedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process submission', [
                'submission_id' => $this->submission->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle rejected attachments
     */
    private function handleRejectedAttachments(): void
    {
        // Уведомляем автора о проблемах с файлами
        Notification::create([
            'user_id' => $this->submission->user_id,
            'type' => 'submission_issues',
            'data' => [
                'message' => 'В вашей работе есть файлы, не прошедшие проверку',
                'submission_id' => $this->submission->id,
                'submission_title' => $this->submission->title,
                'rejected_count' => $this->submission->attachments->where('status', 'rejected')->count(),
            ],
        ]);
    }

    /**
     * Notify jury about ready submission
     */
    private function notifyJury(): void
    {
        $juryUsers = User::where('role', User::ROLE_JURY)->get();

        foreach ($juryUsers as $jury) {
            Notification::create([
                'user_id' => $jury->id,
                'type' => 'submission_ready_for_review',
                'data' => [
                    'message' => "Работа \"{$this->submission->title}\" готова к проверке",
                    'submission_id' => $this->submission->id,
                    'submission_title' => $this->submission->title,
                    'user_name' => $this->submission->user->name,
                ],
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $e): void
    {
        Log::critical('ProcessSubmissionJob failed permanently', [
            'submission_id' => $this->submission->id,
            'error' => $e->getMessage()
        ]);

        // Уведомляем админов о проблеме
        $admins = User::where('role', User::ROLE_ADMIN)->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'system_error',
                'data' => [
                    'message' => "Ошибка при обработке работы #{$this->submission->id}",
                    'submission_id' => $this->submission->id,
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }
}
