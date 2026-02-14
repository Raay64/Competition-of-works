<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Администраторы
        User::create([
            'name' => 'Администратор Системы',
            'email' => 'admin@contest.ru',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'phone' => '+7 (999) 123-45-67',
            'organization' => 'Оргкомитет',
            'position' => 'Главный администратор',
            'bio' => 'Отвечаю за работу всей платформы',
        ]);

        User::create([
            'name' => 'Технический Администратор',
            'email' => 'tech@contest.ru',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'phone' => '+7 (999) 765-43-21',
            'organization' => 'IT отдел',
            'position' => 'Системный администратор',
        ]);

        // Члены жюри
        $juryMembers = [
            [
                'name' => 'Иван Петров',
                'email' => 'ivan.jury@contest.ru',
                'phone' => '+7 (901) 234-56-78',
                'organization' => 'МГУ',
                'position' => 'Профессор',
                'bio' => 'Доктор наук, эксперт в области информационных технологий',
            ],
            [
                'name' => 'Елена Соколова',
                'email' => 'elena.jury@contest.ru',
                'phone' => '+7 (902) 345-67-89',
                'organization' => 'СПбГУ',
                'position' => 'Доцент',
                'bio' => 'Кандидат наук, специалист по дизайну',
            ],
            [
                'name' => 'Алексей Морозов',
                'email' => 'alexey.jury@contest.ru',
                'phone' => '+7 (903) 456-78-90',
                'organization' => 'ИТМО',
                'position' => 'Ведущий научный сотрудник',
                'bio' => 'Эксперт по инновационным проектам',
            ],
            [
                'name' => 'Мария Волкова',
                'email' => 'maria.jury@contest.ru',
                'phone' => '+7 (904) 567-89-01',
                'organization' => 'ВШЭ',
                'position' => 'Профессор',
                'bio' => 'Специалист по анализу данных',
            ],
        ];

        foreach ($juryMembers as $jury) {
            User::create(array_merge($jury, [
                'password' => Hash::make('password'),
                'role' => User::ROLE_JURY,
                'email_verified_at' => now(),
            ]));
        }

        // Участники (20 человек)
        $participants = [
            ['name' => 'Александр Иванов', 'email' => 'alex.ivanov@example.com'],
            ['name' => 'Дмитрий Смирнов', 'email' => 'dmitry.smirnov@example.com'],
            ['name' => 'Максим Кузнецов', 'email' => 'maxim.kuznetsov@example.com'],
            ['name' => 'Артем Попов', 'email' => 'artem.popov@example.com'],
            ['name' => 'Илья Васильев', 'email' => 'ilya.vasiliev@example.com'],
            ['name' => 'Анна Новикова', 'email' => 'anna.novikova@example.com'],
            ['name' => 'Екатерина Морозова', 'email' => 'ekaterina.morozova@example.com'],
            ['name' => 'Ольга Волкова', 'email' => 'olga.volkova@example.com'],
            ['name' => 'Татьяна Павлова', 'email' => 'tatyana.pavlova@example.com'],
            ['name' => 'Наталья Соколова', 'email' => 'natalya.sokolova@example.com'],
            ['name' => 'Сергей Михайлов', 'email' => 'sergey.mikhailov@example.com'],
            ['name' => 'Андрей Федоров', 'email' => 'andrey.fedorov@example.com'],
            ['name' => 'Павел Алексеев', 'email' => 'pavel.alekseev@example.com'],
            ['name' => 'Николай Лебедев', 'email' => 'nikolay.lebedev@example.com'],
            ['name' => 'Владимир Семенов', 'email' => 'vladimir.semenov@example.com'],
            ['name' => 'Юлия Зайцева', 'email' => 'yulia.zaytseva@example.com'],
            ['name' => 'Анастасия Воробьева', 'email' => 'anastasia.vorobieva@example.com'],
            ['name' => 'Ксения Филиппова', 'email' => 'ksenia.filippova@example.com'],
            ['name' => 'Дарья Егорова', 'email' => 'darya.egorova@example.com'],
            ['name' => 'Виктория Андреева', 'email' => 'viktoria.andreeva@example.com'],
        ];

        foreach ($participants as $index => $participant) {
            User::create([
                'name' => $participant['name'],
                'email' => $participant['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_PARTICIPANT,
                'email_verified_at' => $index < 15 ? now() : null,
                'last_login_at' => $index < 10 ? now()->subDays(rand(0, 30)) : null,
                'phone' => $index < 15 ? '+7 (9' . rand(10, 99) . ') ' . rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10, 99) : null,
                'organization' => $index < 12 ? ['Школа №' . rand(1, 50), 'Университет', 'Колледж'][rand(0, 2)] : null,
                'position' => $index < 8 ? ['Студент', 'Ученик', 'Магистрант'][rand(0, 2)] : null,
                'bio' => $index < 5 ? 'Участник конкурса с большим опытом' : null,
            ]);
        }

        // Подсчет созданных пользователей
        $totalUsers = User::count();
        $this->command->info("Создано {$totalUsers} пользователей");
    }
}
