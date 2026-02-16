<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Конкурс работ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-indigo-100 to-purple-100 min-h-screen flex items-center justify-center py-12">
<div class="max-w-md w-full mx-4">
    <!-- Заголовок -->
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <div class="inline-flex items-center space-x-2 text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left"></i>
                <span>На главную</span>
            </div>
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-4">Регистрация</h1>
        <p class="text-gray-600 mt-2">Создайте аккаунт для участия в конкурсах</p>
    </div>

    <!-- Форма регистрации -->
    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
        <div class="p-8">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Имя -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-indigo-500"></i>Имя
                    </label>
                    <input type="text" name="name" id="name"
                           value="{{ old('name') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('name') border-red-500 @enderror"
                           placeholder="Иван Петров"
                           required autofocus>
                    @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-indigo-500"></i>Email
                    </label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                           placeholder="your@email.com"
                           required>
                    @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Пароль -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-indigo-500"></i>Пароль
                    </label>
                    <input type="password" name="password" id="password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('password') border-red-500 @enderror"
                           placeholder="Минимум 8 символов"
                           required>
                    @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Подтверждение пароля -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-indigo-500"></i>Подтверждение пароля
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           placeholder="Введите пароль еще раз"
                           required>
                </div>

                <!-- Согласие с условиями -->
                <div class="mb-6">
                    <div class="flex items-start">
                        <input type="checkbox" name="terms" id="terms"
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                               required>
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            Я соглашаюсь с
                            <a href="#" class="text-indigo-600 hover:text-indigo-800">условиями использования</a>
                            и
                            <a href="#" class="text-indigo-600 hover:text-indigo-800">политикой конфиденциальности</a>
                        </label>
                    </div>
                </div>

                <!-- Кнопка регистрации -->
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i>
                    Зарегистрироваться
                </button>
            </form>

            <!-- Ссылка на вход -->
            <p class="text-center mt-6 text-sm text-gray-600">
                Уже есть аккаунт?
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                    Войти
                </a>
            </p>
        </div>
    </div>

    <!-- Преимущества -->
    <div class="mt-8 grid grid-cols-3 gap-4">
        <div class="text-center">
            <div class="inline-block p-3 bg-indigo-100 rounded-full mb-2">
                <i class="fas fa-file-alt text-indigo-600"></i>
            </div>
            <p class="text-xs text-gray-600">Подача работ</p>
        </div>
        <div class="text-center">
            <div class="inline-block p-3 bg-indigo-100 rounded-full mb-2">
                <i class="fas fa-comments text-indigo-600"></i>
            </div>
            <p class="text-xs text-gray-600">Обратная связь</p>
        </div>
        <div class="text-center">
            <div class="inline-block p-3 bg-indigo-100 rounded-full mb-2">
                <i class="fas fa-trophy text-indigo-600"></i>
            </div>
            <p class="text-xs text-gray-600">Участие в конкурсах</p>
        </div>
    </div>
</div>
</body>
</html>
