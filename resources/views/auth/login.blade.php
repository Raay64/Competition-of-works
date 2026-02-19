<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему - Конкурс работ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-indigo-100 to-purple-100 min-h-screen flex items-center justify-center">
<div class="max-w-md w-full mx-4">
    <!-- Логотип -->
    <div class="text-center mb-8">
        <div class="inline-block p-4 bg-white rounded-full shadow-lg mb-4">
            <i class="fas fa-trophy text-4xl text-indigo-600"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Конкурс работ</h1>
        <p class="text-gray-600 mt-2">Войдите в систему для продолжения</p>
    </div>

    <!-- Форма входа -->
    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
        <div class="p-8">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-indigo-500"></i>Email
                    </label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                           placeholder="your@email.com"
                           required autofocus>
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
                           placeholder="••••••••"
                           required>
                    @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Запомнить меня -->
                <div class="flex items-center justify-between mb-2">
                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Забыли пароль?
                    </a>
                </div>

                <!-- Кнопка входа -->
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Войти
                </button>
            </form>

            <!-- Ссылка на регистрацию -->
            <p class="text-center mt-6 text-sm text-gray-600">
                Нет аккаунта?
                <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                    Зарегистрироваться
                </a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
