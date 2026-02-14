<?php

namespace Database\Seeders;

use App\Models\SubmissionComment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $submissions = Submission::whereIn('status', ['submitted', 'needs_fix', 'accepted', 'rejected'])->get();
        $users = User::all();
        $jury = User::where('role', User::ROLE_JURY)->get();

        $commentTexts = [
            'participant' => [
                'Спасибо за обратную связь!',
                'Я исправил все замечания, проверьте пожалуйста',
                'Можете уточнить, что именно нужно поправить?',
                'Спасибо за высокую оценку!',
                'Я учту все пожелания',
                'Файлы обновлены',
                'Скажите, пожалуйста, когда будет результат?',
            ],
            'jury' => [
                'Отличная работа! Особенно понравилось...',
                'Требуется доработка в разделе...',
                'Хорошая идея, но нужно улучшить реализацию',
                'Принято. Поздравляю!',
                'К сожалению, работа не соответствует требованиям',
                'Обратите внимание на оформление',
                'Нужно добавить больше практических примеров',
                'В целом хорошо, но есть несколько замечаний...',
            ],
            'admin' => [
                'Проверьте, пожалуйста, файлы',
                'Напоминаю о дедлайне',
                'Все документы в порядке',
            ],
        ];

        foreach ($submissions as $submission) {
            // Количество комментариев от 0 до 5
            $numComments = rand(0, 5);

            for ($i = 0; $i < $numComments; $i++) {
                // Выбираем автора комментария
                if ($i % 3 == 0 && $jury->isNotEmpty()) {
                    // Комментарий от жюри
                    $author = $jury->random();
                    $text = $commentTexts['jury'][array_rand($commentTexts['jury'])];
                } elseif ($i % 5 == 0) {
                    // Комментарий от админа
                    $author = User::where('role', User::ROLE_ADMIN)->inRandomOrder()->first();
                    $text = $commentTexts['admin'][array_rand($commentTexts['admin'])];
                } else {
                    // Комментарий от автора работы или другого участника
                    $author = rand(0, 3) == 0 ? User::where('role', User::ROLE_PARTICIPANT)->inRandomOrder()->first() : $submission->user;
                    $text = $commentTexts['participant'][array_rand($commentTexts['participant'])];
                }

                // Для некоторых комментариев создаем ответы
                $comment = SubmissionComment::create([
                    'submission_id' => $submission->id,
                    'user_id' => $author->id,
                    'body' => $text . ' (#' . ($i + 1) . ')',
                    'is_helpful' => rand(0, 10) == 0,
                    'created_at' => $submission->created_at->addHours(rand(1, 48)),
                ]);

                // С вероятностью 30% добавляем ответ
                if (rand(1, 100) <= 30) {
                    $responder = $comment->user_id == $submission->user_id ? $jury->random() : $submission->user;

                    SubmissionComment::create([
                        'submission_id' => $submission->id,
                        'user_id' => $responder->id,
                        'parent_id' => $comment->id,
                        'body' => 'Ответ на комментарий: ' . $text,
                        'created_at' => $comment->created_at->addHours(rand(1, 12)),
                    ]);
                }
            }
        }

        $totalComments = SubmissionComment::count();
        $this->command->info("Создано {$totalComments} комментариев");
    }
}
