<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Платформа для сбора работ на конкурс</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .feature-card {
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="font-sans antialiased">
<!-- Навигация -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-trophy text-2xl text-indigo-600"></i>
                </div>
                <div class="ml-4 text-xl font-bold text-gray-800">
                    Конкурс работ
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-indigo-600 hover:text-indigo-800 font-medium">
                    Войти
                </a>
                <a href="{{ route('register') }}"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                    Регистрация
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div class="gradient-bg text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <h1 class="text-5xl font-bold mb-6">
                Платформа для сбора работ на конкурс
            </h1>
            <p class="text-xl mb-12 max-w-3xl mx-auto opacity-90">
                Удобный инструмент для организации конкурсов, сбора заявок,
                проверки работ и обратной связи с участниками
            </p>
            <div class="space-x-4">
                <a href="{{ route('register') }}"
                   class="inline-block px-8 py-4 bg-white text-indigo-600 rounded-lg font-bold hover:bg-opacity-90 transition transform hover:scale-105">
                    Начать участие
                </a>
                <a href="#features"
                   class="inline-block px-8 py-4 border-2 border-white text-white rounded-lg font-bold hover:bg-white hover:text-indigo-600 transition">
                    Узнать больше
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Features -->
<div id="features" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                Возможности платформы
            </h2>
            <p class="text-xl text-gray-600">
                Все необходимое для успешного проведения конкурса
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Для участников -->
            <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-user text-2xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Для участников</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Создание и редактирование работ
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Загрузка файлов (PDF, ZIP, PNG, JPG)
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Отслеживание статуса
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Комментарии от жюри
                    </li>
                </ul>
            </div>

            <!-- Для жюри -->
            <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-gavel text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Для жюри</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Просмотр всех работ
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Изменение статусов
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Комментирование работ
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Запрос доработок
                    </li>
                </ul>
            </div>

            <!-- Для администраторов -->
            <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-cog text-2xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Для администраторов</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Управление конкурсами
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Управление пользователями
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Назначение ролей
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Статистика и отчеты
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Как это работает -->
<div class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                Как это работает
            </h2>
            <p class="text-xl text-gray-600">
                Простой и понятный процесс в несколько шагов
            </p>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">1</div>
                <h3 class="font-bold mb-2">Регистрация</h3>
                <p class="text-gray-600 text-sm">Создайте аккаунт участника</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">2</div>
                <h3 class="font-bold mb-2">Выбор конкурса</h3>
                <p class="text-gray-600 text-sm">Выберите активный конкурс</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">3</div>
                <h3 class="font-bold mb-2">Загрузка работы</h3>
                <p class="text-gray-600 text-sm">Добавьте описание и файлы</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">4</div>
                <h3 class="font-bold mb-2">Получение результата</h3>
                <p class="text-gray-600 text-sm">Следите за статусом работы</p>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-lg font-bold mb-4">О платформе</h3>
                <p class="text-gray-400 text-sm">
                    Современное решение для организации конкурсов и сбора работ участников.
                </p>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Быстрые ссылки</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-gray-400 hover:text-white">Главная</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">О нас</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">Контакты</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Поддержка</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-gray-400 hover:text-white">FAQ</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">Помощь</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">Техподдержка</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Контакты</h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><i class="fas fa-envelope mr-2"></i> support@contest.ru</li>
                    <li><i class="fas fa-phone mr-2"></i> +7 (999) 123-45-67</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-400">
            © {{ date('Y') }} Платформа «Сбор работ на конкурс». Все права защищены.
        </div>
    </div>
</footer>
</body>
</html>
