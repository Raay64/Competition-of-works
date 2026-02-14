<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use App\Models\Submission;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $submissions = Submission::all();

        $notificationTypes = [
            'status_changed' => 'Статус вашей работы изменен',
            'new_comment' => 'Новый комментарий к вашей работе',
            'deadline_approaching' => 'Приближается дедлайн',
            'submission_created' => 'Создана новая работа',
            'attachment_scanned' => 'Файл проверен',
            'attachment_rejected' => 'Файл отклонен',
            'submission_finalized' => 'Работа получила окончательный статус',
            'participant_response' => 'Участник ответил на замечания',
        ];

        foreach ($users as $user) {
            // От 3 до 15 уведомлений на пользователя
            $numNotifications = rand(3, 15);

            for ($i = 0; $i < $numNotifications; $i++) {
                $type = array_rand($notificationTypes);
                $submission = $submissions->random();

                $data = [
                    'message' => $notificationTypes[$type] . ': ' . $submission->title,
                    'submission_id' => $submission->id,
                    'submission_title' => $submission->title,
                ];

                // Добавляем специфичные для типа данные
                switch ($type) {
                    case 'status_changed':
                        $data['old_status'] = 'submitted';
                        $data['new_status'] = ['accepted', 'rejected', 'needs_fix'][rand(0, 2)];
                        $data['contest_title'] = $submission->contest->title;
                        break;
                    case 'new_comment':
                        $data['commenter_name'] = User::inRandomOrder()->first()->name;
                        $data['comment_body'] = 'Текст комментария...';
                        break;
                    case 'attachment_rejected':
                        $data['attachment_name'] = 'document.pdf';
                        $data['rejection_reason'] = 'Файл не соответствует требованиям';
                        break;
                }

                $readAt = rand(0, 1) ? now()->subHours(rand(1, 48)) : null;

                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'data' => $data,
                    'read_at' => $readAt,
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $totalNotifications = Notification::count();
        $this->command->info("Создано {$totalNotifications} уведомлений");
    }
}
