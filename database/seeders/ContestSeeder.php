<?php

namespace Database\Seeders;

use App\Models\Contest;
use Illuminate\Database\Seeder;

class ContestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contests = [
            [
                'title' => 'Инновационные IT-проекты 2026',
                'description' => 'Конкурс для молодых разработчиков и IT-специалистов. Принимаются проекты в области веб-разработки, мобильных приложений, искусственного интеллекта и интернета вещей.',
                'deadline_at' => now()->addDays(45),
                'is_active' => true,
            ],
            [
                'title' => 'Дизайн будущего',
                'description' => 'Конкурс графического дизайна и визуальных искусств. Работы принимаются в номинациях: плакат, фирменный стиль, цифровая иллюстрация.',
                'deadline_at' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'title' => 'Научные исследования школьников',
                'description' => 'Конкурс научно-исследовательских работ для учащихся 9-11 классов. Темы: физика, химия, биология, экология.',
                'deadline_at' => now()->addDays(60),
                'is_active' => true,
            ],
            [
                'title' => 'Стартап-идеи 2026',
                'description' => 'Конкурс бизнес-проектов и стартап-идей. Оценивается инновационность, реализуемость и рыночный потенциал.',
                'deadline_at' => now()->addDays(90),
                'is_active' => true,
            ],
            [
                'title' => 'Литературный конкурс "Молодое перо"',
                'description' => 'Конкурс для начинающих писателей и поэтов. Номинации: рассказ, стихотворение, эссе.',
                'deadline_at' => now()->addDays(20),
                'is_active' => true,
            ],
            [
                'title' => 'Весенний марафон 2026',
                'description' => 'Прошедший конкурс для архива',
                'deadline_at' => now()->subDays(30),
                'is_active' => false,
            ],
            [
                'title' => 'Зимний конкурс 2026',
                'description' => 'Завершенный конкурс с большим количеством участников',
                'deadline_at' => now()->subDays(60),
                'is_active' => false,
            ],
        ];

        foreach ($contests as $contest) {
            Contest::create($contest);
        }

        $this->command->info('Создано ' . count($contests) . ' конкурсов');
    }
}
