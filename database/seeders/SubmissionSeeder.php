<?php

namespace Database\Seeders;

use App\Models\Submission;
use App\Models\User;
use App\Models\Contest;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $participants = User::where('role', User::ROLE_PARTICIPANT)->get();
        $contests = Contest::where('is_active', true)->get();
        $allContests = Contest::all();

        $titles = [
            'Разработка мобильного приложения для обучения',
            'Исследование влияния социальных сетей',
            'Дизайн-концепция городского пространства',
            'Искусственный интеллект в медицине',
            'Экологический мониторинг через спутники',
            'Образовательная платформа для детей',
            'Роботизированная рука-манипулятор',
            'Виртуальная экскурсия по музею',
            'Система распознавания эмоций',
            'Блокчейн в голосовании',
            '3D-модель исторического здания',
            'Игра для развития памяти',
            'Анализ рынка недвижимости',
            'Энергоэффективный дом будущего',
            'Умная теплица на Arduino',
        ];

        $descriptions = [
            'Подробное описание проекта с обоснованием актуальности...',
            'В работе представлены результаты исследования...',
            'Проект включает в себя теоретическую и практическую части...',
            'Целью работы является создание инновационного решения...',
            'В ходе выполнения были решены следующие задачи...',
            'Работа выполнена под руководством опытного наставника...',
            'Проект прошел тестирование на фокус-группе...',
            'Разработано полностью самостоятельно за 3 месяца...',
            'Использованы современные технологии и подходы...',
            'Результаты могут быть применены в различных сферах...',
        ];

        $statuses = ['draft', 'submitted', 'needs_fix', 'accepted', 'rejected'];

        // Создаем работы для активных конкурсов
        foreach ($contests as $contest) {
            // Каждый участник подает от 0 до 3 работ на конкурс
            foreach ($participants as $participant) {
                $numSubmissions = rand(0, 3);

                for ($i = 0; $i < $numSubmissions; $i++) {
                    $status = $statuses[array_rand($statuses)];

                    Submission::create([
                        'contest_id' => $contest->id,
                        'user_id' => $participant->id,
                        'title' => $titles[array_rand($titles)] . ' #' . rand(100, 999),
                        'description' => $descriptions[array_rand($descriptions)],
                        'status' => $status,
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => $status !== 'draft' ? now()->subDays(rand(1, 10)) : now()->subDays(rand(1, 30)),
                    ]);
                }
            }
        }

        // Создаем работы для завершенных конкурсов
        foreach ($allContests->where('is_active', false) as $contest) {
            for ($i = 0; $i < 50; $i++) {
                $participant = $participants->random();
                $status = $statuses[array_rand($statuses)];

                Submission::create([
                    'contest_id' => $contest->id,
                    'user_id' => $participant->id,
                    'title' => $titles[array_rand($titles)] . ' (архив)',
                    'description' => $descriptions[array_rand($descriptions)],
                    'status' => $status,
                    'created_at' => now()->subDays(rand(60, 120)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $totalSubmissions = Submission::count();
        $this->command->info("Создано {$totalSubmissions} работ");

        // Статистика по статусам
        foreach ($statuses as $status) {
            $count = Submission::where('status', $status)->count();
            $this->command->info("  {$status}: {$count}");
        }
    }
}
