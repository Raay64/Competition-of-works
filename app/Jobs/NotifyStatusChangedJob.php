<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyStatusChangedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Submission $submission;
    protected string $oldStatus;
    public $timeout = 30;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Submission $submission, string $oldStatus)
    {
        $this->submission = $submission;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->submission->user;
        $statusNames = [
            'draft' => 'Черновик',
            'submitted' => 'На проверке',
            'needs_fix' => 'Требуется доработка',
            'accepted' => 'Принято',
            'rejected' => 'Отклонено'
        ];

        $data = [
            'submission_id' => $this->submission->id,
            'submission_title' => $this->submission->title,
            'old_status' => $this->oldStatus,
            'old_status_name' => $statusNames[$this->oldStatus] ?? $this->oldStatus,
            'new_status' => $this->submission->status,
            'new_status_name' => $statusNames[$this->submission->status] ?? $this->submission->status,
            'changed_at' => now()->toDateTimeString(),
            'contest_title' => $this->submission->contest->title ?? 'Без конкурса',
        ];

        // 1. Сохраняем в таблицу notifications
        Notification::create([
            'user_id' => $user->id,
            'type' => 'status_changed',
            'data' => [
                'message' => "Статус вашей работы \"{$this->submission->title}\" изменен на \"{$statusNames[$this->submission->status]}\"",
                'submission_id' => $this->submission->id,
                'submission_title' => $this->submission->title,
                'old_status' => $this->oldStatus,
                'new_status' => $this->submission->status,
                'contest_title' => $this->submission->contest->title ?? 'Без конкурса',
            ],
        ]);

        // 2. Логируем
        Log::info('Status changed notification', [
            'user_id' => $user->id,
            'email' => $user->email,
            'submission_id' => $this->submission->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->submission->status,
        ]);

        // 3. Отправляем email, если пользователь хочет получать уведомления
        if ($user->wantsEmailOnStatusChange()) {
            try {
                // В реальном проекте здесь была бы отправка email
                // Mail::send('emails.status-changed', ['data' => $data], function ($message) use ($user) {
                //     $message->to($user->email)
                //             ->subject('Статус вашей работы изменен');
                // });

                Log::info('Status change email would be sent', ['user' => $user->email]);
            } catch (\Exception $e) {
                Log::error('Failed to send status change email', [
                    'user' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 4. Уведомляем админов о важных изменениях
        if (in_array($this->submission->status, ['accepted', 'rejected'])) {
            $admins = User::where('role', User::ROLE_ADMIN)->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'submission_finalized',
                    'data' => [
                        'message' => "Работа \"{$this->submission->title}\" получила окончательный статус: {$statusNames[$this->submission->status]}",
                        'submission_id' => $this->submission->id,
                        'submission_title' => $this->submission->title,
                        'user_name' => $user->name,
                        'status' => $this->submission->status,
                    ],
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $e): void
    {
        Log::error('NotifyStatusChangedJob failed', [
            'submission_id' => $this->submission->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->submission->status,
            'error' => $e->getMessage()
        ]);
    }
}
