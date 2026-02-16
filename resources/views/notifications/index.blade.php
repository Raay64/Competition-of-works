@extends('layouts.app')

@section('title', 'Уведомления')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-bell mr-2 text-indigo-500"></i>
                        Уведомления
                    </h1>
                    <p class="text-gray-600 mt-1">Все ваши уведомления в одном месте</p>
                </div>
                <div class="flex space-x-2">
                    @if($stats['unread'] > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                <i class="fas fa-check-double mr-2"></i>
                                Отметить все как прочитанные
                            </button>
                        </form>
                    @endif

                    @if($stats['total'] > 0)
                        <form action="{{ route('notifications.clear-all') }}" method="POST"
                              onsubmit="return confirm('Вы уверены? Все уведомления будут удалены.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                <i class="fas fa-trash mr-2"></i>
                                Очистить все
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                    </div>
                    <i class="fas fa-bell text-4xl text-gray-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Непрочитанные</p>
                        <p class="text-3xl font-bold text-indigo-600">{{ $stats['unread'] }}</p>
                    </div>
                    <i class="fas fa-bell text-4xl text-indigo-300"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Прочитанные</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['read'] }}</p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-300"></i>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="GET" action="{{ route('notifications.index') }}" class="flex space-x-4">
                <div class="flex-1">
                    <select name="filter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Все уведомления</option>
                        <option value="unread" {{ request('filter') == 'unread' ? 'selected' : '' }}>Непрочитанные</option>
                        <option value="read" {{ request('filter') == 'read' ? 'selected' : '' }}>Прочитанные</option>
                    </select>
                </div>

                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Поиск по уведомлениям..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-search mr-2"></i>
                    Применить
                </button>

                <a href="{{ route('notifications.index') }}"
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-times"></i>
                </a>
            </form>
        </div>

        <!-- Список уведомлений -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            @if($notifications->isEmpty())
                <div class="text-center py-16">
                    <i class="fas fa-bell-slash text-5xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Нет уведомлений</h3>
                    <p class="text-gray-500">У вас пока нет уведомлений</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($notifications as $notification)
                        <div class="p-6 hover:bg-gray-50 transition {{ !$notification->read_at ? 'bg-indigo-50' : '' }}">
                            <div class="flex items-start space-x-4">
                                <!-- Иконка типа уведомления -->
                                <div class="flex-shrink-0">
                                    @php
                                        $typeIcons = [
                                            'status_changed' => ['icon' => 'fa-exchange-alt', 'color' => 'blue'],
                                            'new_comment' => ['icon' => 'fa-comment', 'color' => 'green'],
                                            'deadline_approaching' => ['icon' => 'fa-clock', 'color' => 'yellow'],
                                            'submission_received' => ['icon' => 'fa-file-alt', 'color' => 'purple'],
                                        ];
                                        $type = $notification->type;
                                        $icon = $typeIcons[$type]['icon'] ?? 'fa-bell';
                                        $color = $typeIcons[$type]['color'] ?? 'gray';
                                    @endphp
                                    <div class="w-10 h-10 bg-{{ $color }}-100 rounded-full flex items-center justify-center">
                                        <i class="fas {{ $icon }} text-{{ $color }}-600"></i>
                                    </div>
                                </div>

                                <!-- Содержимое уведомления -->
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-gray-900">
                                                {{ $notification->data['message'] ?? 'Новое уведомление' }}
                                            </p>
                                            @if(isset($notification->data['submission_title']))
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Работа: {{ $notification->data['submission_title'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                    </div>

                                    <!-- Действия с уведомлением -->
                                    <div class="mt-2 flex space-x-2">
                                        <a href="{{ route('notifications.show', $notification) }}"
                                           class="text-xs text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-eye mr-1"></i>
                                            Подробнее
                                        </a>

                                        @if(!$notification->read_at)
                                            <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs text-green-600 hover:text-green-800">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Отметить как прочитанное
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('notifications.destroy', $notification) }}"
                                              method="POST"
                                              onsubmit="return confirm('Удалить уведомление?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash mr-1"></i>
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Индикатор непрочитанного -->
                                @if(!$notification->read_at)
                                    <div class="flex-shrink-0">
                                        <span class="w-2 h-2 bg-indigo-600 rounded-full block"></span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Пагинация -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $notifications->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
