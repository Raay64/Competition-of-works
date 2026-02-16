<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Конкурс работ') - Платформа</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom Styles -->
    @stack('styles')

    <style>
        .transition-all {
            transition: all 0.3s ease;
        }
        .hover-scale:hover {
            transform: scale(1.02);
        }
        .status-badge {
            @apply px-2 py-1 text-xs font-medium rounded-full;
        }
        .status-draft { @apply bg-gray-100 text-gray-800; }
        .status-submitted { @apply bg-yellow-100 text-yellow-800; }
        .status-needs_fix { @apply bg-orange-100 text-orange-800; }
        .status-accepted { @apply bg-green-100 text-green-800; }
        .status-rejected { @apply bg-red-100 text-red-800; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
<!-- Навигация -->
<nav class="bg-white shadow-lg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Левая часть навигации -->
            <div class="flex">
                <!-- Логотип -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600 hover:text-indigo-800 transition">
                        <i class="fas fa-trophy mr-2"></i>
                        Конкурс работ
                    </a>
                </div>

                <!-- Основное меню -->
                <div class="hidden md:ml-6 md:flex md:space-x-8">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }}">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Дашборд
                    </a>

                    <a href="{{ route('contests.index') }}"
                       class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('contests.*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }}">
                        <i class="fas fa-list mr-2"></i>
                        Конкурсы
                    </a>

                    @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                        <a href="{{ route('submissions.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('submissions.index') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }}">
                            <i class="fas fa-tasks mr-2"></i>
                            Все работы
                        </a>
                    @else
                        <a href="{{ route('submissions.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('submissions.*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }}">
                            <i class="fas fa-file-alt mr-2"></i>
                            Мои работы
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('users.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('users.*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }}">
                            <i class="fas fa-users mr-2"></i>
                            Пользователи
                        </a>

                        <a href="{{ route('statistics') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('statistics') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }}">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Статистика
                        </a>
                    @endif
                </div>
            </div>

            <!-- Правая часть навигации -->
            <div class="flex items-center space-x-4">
                <!-- Уведомления -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-indigo-600 focus:outline-none">
                        <i class="fas fa-bell text-xl"></i>
                        @php
                            $unreadCount = auth()->user()->notifications()->whereNull('read_at')->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                        @endif
                    </button>

                    <!-- Выпадающее меню уведомлений -->
                    <div x-show="open" @click.away="open = false"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-20"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95">

                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-gray-700">Уведомления</h3>
                                <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800">
                                    Все уведомления
                                </a>
                            </div>
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            @php
                                $recentNotifications = auth()->user()
                                    ->notifications()
                                    ->latest()
                                    ->take(5)
                                    ->get();
                            @endphp

                            @forelse($recentNotifications as $notification)
                                <a href="{{ route('notifications.show', $notification) }}"
                                   class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition {{ !$notification->read_at ? 'bg-indigo-50' : '' }}">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            @if($notification->type == 'status_changed')
                                                <i class="fas fa-exchange-alt text-blue-500"></i>
                                            @elseif($notification->type == 'new_comment')
                                                <i class="fas fa-comment text-green-500"></i>
                                            @else
                                                <i class="fas fa-bell text-gray-400"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900">
                                                {{ $notification->data['message'] ?? 'Новое уведомление' }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        @if(!$notification->read_at)
                                            <span class="w-2 h-2 bg-indigo-600 rounded-full"></span>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-center">
                                    <i class="fas fa-bell-slash text-gray-400 text-3xl mb-2"></i>
                                    <p class="text-sm text-gray-500">Нет уведомлений</p>
                                </div>
                            @endforelse
                        </div>

                        @if($unreadCount > 0)
                            <div class="px-4 py-2 bg-gray-50 border-t border-gray-200">
                                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 w-full text-center">
                                        Отметить все как прочитанные
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Профиль пользователя -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none group">
                        <div class="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-md group-hover:shadow-lg transition">
                                <span class="text-white text-sm font-medium">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                        </div>
                        <span class="hidden md:block text-sm font-medium text-gray-700 group-hover:text-indigo-600">
                                {{ auth()->user()->name }}
                            </span>
                        <i class="fas fa-chevron-down text-xs text-gray-500 group-hover:text-indigo-600"></i>
                    </button>

                    <!-- Выпадающее меню профиля -->
                    <div x-show="open" @click.away="open = false"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95">

                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            <p class="text-xs font-medium text-indigo-600 mt-1">
                                @if(auth()->user()->isAdmin())
                                    Администратор
                                @elseif(auth()->user()->isJury())
                                    Член жюри
                                @else
                                    Участник
                                @endif
                            </p>
                        </div>

                        <a href="{{ route('users.show', auth()->user()) }}"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i> Мой профиль
                        </a>

                        <a href="{{ route('notifications.settings') }}"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-cog mr-2"></i> Настройки
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                            @csrf
                            <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Выйти
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
@if(session('success'))
    <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    </div>
@endif

<!-- Main Content -->
<main class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @yield('content')
    </div>
</main>

<!-- Footer -->
<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500">
            © {{ date('Y') }} Платформа «Сбор работ на конкурс». Все права защищены.
        </p>
    </div>
</footer>

<!-- Alpine.js -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>

<!-- jQuery (для некоторых функций) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom Scripts -->
@stack('scripts')

<script>
    // Автоматическое скрытие flash-сообщений через 5 секунд
    setTimeout(function() {
        document.querySelectorAll('[role="alert"]').forEach(function(el) {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function() {
                el.remove();
            }, 500);
        });
    }, 5000);
</script>
</body>
</html>
