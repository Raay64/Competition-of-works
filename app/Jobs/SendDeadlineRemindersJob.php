<?php

namespace App\Jobs;

use App\Models\Contest;
use App\Models\Notification;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDeadlineRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting deadline reminders job');

        // Находим конкурсы, у которых дедлайн через 3 дня
        $upcomingContests = Contest::where('is_active', true)
            ->where('deadline_at', '>', now())
            ->where('deadline_at', '<', now()->addDays(3))
            ->get();

        foreach ($upcomingContests as $contest) {
            $this->remindAboutContestDeadline($contest);
        }

        // Находим конкурсы, у которых дедлайн сегодня
        $todayContests = Contest::where('is_active', true)
            ->whereDate('deadline_at', now()->toDateString())
            ->get();

        foreach ($todayContests as $contest) {
            $this->remindAboutTodayDeadline($contest);
        }

        // Находим конкурсы, у которых дедлайн прошел
        $passedContests = Contest::where('is_active', true)
            ->where('deadline_at', '<', now())
            ->where('deadline_at', '>', now()->subDay())
            ->get();

        foreach ($passedContests as $contest) {
            $this->notifyAboutPassedDeadline($contest);
        }

        Log::info('Deadline reminders job completed');
    }

    /**
     * Remind about contest deadline in 3 days
     */
    private function remindAboutContestDeadline(Contest $contest): void
    {
        // Находим участников, которые еще не подали работы
        $participants = User::where('role', User::ROLE_PARTICIPANT)->get();

        foreach ($participants as $participant) {
            $hasSubmission = Submission::where('contest_id', $contest->id)
                ->where('user_id', $participant->id)
                ->exists();

            if (!$hasSubmission) {
                Notification::create([
                    'user_id' => $participant->id,
                    'type' => 'deadline_approaching',
                    'data' => [
                        'message' => "До дедлайна конкурса \"{$contest->title}\" осталось 3 дня",
                        'contest_id' => $contest->id,
                        'contest_title' => $contest->title,
                        'deadline_at' => $contest->deadline_at->format('d.m.Y H:i'),
                    ],
                ]);
            }
        }
    }

    /**
     * Remind about today's deadline
     */
    private function remindAboutTodayDeadline(Contest $contest): void
    {
        $participants = User::where('role', User::ROLE_PARTICIPANT)->get();

        foreach ($participants as $participant) {
            $submission = Submission::where('contest_id', $contest->id)
                ->where('user_id', $participant->id)
                ->first();

            if (!$submission) {
                Notification::create([
                    'user_id' => $participant->id,
                    'type' => 'deadline_today',
                    'data' => [
                        'message' => "Сегодня последний день подачи работ на конкурс \"{$contest->title}\"",
                        'contest_id' => $contest->id,
                        'contest_title' => $contest->title,
                    ],
                ]);
            } elseif ($submission->status === 'draft') {
                Notification::create([
                    'user_id' => $participant->id,
                    'type' => 'submission_not_submitted',
                    'data' => [
                        'message' => "У вас есть черновик работы на конкурс \"{$contest->title}\". Не забудьте отправить его до дедлайна!",
                        'submission_id' => $submission->id,
                        'submission_title' => $submission->title,
                        'contest_title' => $contest->title,
                    ],
                ]);
            }
        }
    }

    /**
     * Notify about passed deadline
     */
    private function notifyAboutPassedDeadline(Contest $contest): void
    {
        $submissions = Submission::where('contest_id', $contest->id)
            ->whereIn('status', ['draft', 'submitted'])
            ->get();

        foreach ($submissions as $submission) {
            Notification::create([
                'user_id' => $submission->user_id,
                'type' => 'deadline_passed',
                'data' => [
                    'message' => "Дедлайн конкурса \"{$contest->title}\" прошел. Ваша работа осталась в статусе \"{$submission->status}\"",
                    'submission_id' => $submission->id,
                    'submission_title' => $submission->title,
                    'contest_title' => $contest->title,
                ],
            ]);
        }

        // Уведомляем жюри о необходимости проверить работы
        $juryUsers = User::where('role', User::ROLE_JURY)->get();
        foreach ($juryUsers as $jury) {
            Notification::create([
                'user_id' => $jury->id,
                'type' => 'contest_completed',
                'data' => [
                    'message' => "Конкурс \"{$contest->title}\" завершен. Ожидают проверки: {$contest->submissions()->where('status', 'submitted')->count()} работ",
                    'contest_id' => $contest->id,
                    'contest_title' => $contest->title,
                ],
            ]);
        }
    }
}
