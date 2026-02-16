@extends('layouts.app')

@section('title', 'Панель администратора')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Панель администратора
            </h1>
            <p class="text-gray-600 mt-2">
                Управление конкурсами, пользователями и мониторинг активности.
            </p>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Пользователи</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_users'] }}</p>
                    </div>
                    <i class="fas fa-users text-4xl text-indigo-400"></i>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    <span class="text-green-600">{{ $stats['users_by_role']['participant'] }}</span> участников |
                    <span class="text-blue-600">{{ $stats['users_by_role']['jury'] }}</span> жюри |
                    <span class="text-purple-600">{{ $stats['users_by_role']['admin'] }}</span> админов
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Конкурсы</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_contests'] }}</p>
                    </div>
                    <i class="fas fa-trophy text-4xl text-green-400"></i>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    <span class="text-green-600">{{ $stats['active_contests'] }}</span> активных
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего работ</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_submissions'] }}</p>
                    </div>
                    <i class="fas fa-file-alt text-4xl text-yellow-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Ожидают</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['submissions_by_status']['submitted'] }}</p>
                    </div>
                    <i class="fas fa-clock text-4xl text-purple-400"></i>
                </div>
            </div>
        </div>

        <!-- Детальная статистика по статусам -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach([
                'draft' => ['label' => 'Черновики', 'color' => 'gray'],
                'submitted' => ['label' => 'На проверке', 'color' => 'yellow'],
                'needs_fix' => ['label' => 'Доработка', 'color' => 'orange'],
                'accepted' => ['label' => 'Принято', 'color' => 'green'],
                'rejected' => ['label' => 'Отклонено', 'color' => 'red']
            ] as $status => $info)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-{{ $info['color'] }}-600 text-2xl font-bold">{{ $stats['submissions_by_status'][$status] }}</div>
                    <div class="text-sm text-gray-600">{{ $info['label'] }}</div>
                </div>
            @endforeach
        </div>

        <!-- Быстрые действия -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                Быстрые действия
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('contests.create') }}"
                   class="p-4 border border-gray-200 rounded-lg text-center hover:bg-indigo-50 hover:border-indigo-300 transition group">
                    <i class="fas fa-plus-circle text-2xl text-indigo-500 mb-2 group-hover:scale-110 transition"></i>
                    <div class="text-sm font-medium text-gray-700">Новый конкурс</div>
                </a>
                <a href="{{ route('users.create') }}"
                   class="p-4 border border-gray-200 rounded-lg text-center hover:bg-green-50 hover:border-green-300 transition group">
                    <i class="fas fa-user-plus text-2xl text-green-500 mb-2 group-hover:scale-110 transition"></i>
                    <div class="text-sm font-medium text-gray-700">Новый пользователь</div>
                </a>
                <a href="{{ route('statistics') }}"
                   class="p-4 border border-gray-200 rounded-lg text-center hover:bg-purple-50 hover:border-purple-300 transition group">
                    <i class="fas fa-chart-pie text-2xl text-purple-500 mb-2 group-hover:scale-110 transition"></i>
                    <div class="text-sm font-medium text-gray-700">Статистика</div>
                </a>
                <a href="{{ route('submissions.index', ['status' => 'submitted']) }}"
                   class="p-4 border border-gray-200 rounded-lg text-center hover:bg-yellow-50 hover:border-yellow-300 transition group">
                    <i class="fas fa-clock text-2xl text-yellow-500 mb-2 group-hover:scale-110 transition"></i>
                    <div class="text-sm font-medium text-gray-700">Ожидают проверки</div>
                </a>
            </div>
        </div>

        <!-- Последние работы -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-history mr-2 text-gray-500"></i>
                    Последние работы
                </h2>
                <a href="{{ route('submissions.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                    Все работы <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Работа</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Участник</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Конкурс</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recent_submissions as $submission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('submissions.show', $submission) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $submission->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $submission->user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $submission->contest->title }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'submitted' => 'bg-yellow-100 text-yellow-800',
                                        'needs_fix' => 'bg-orange-100 text-orange-800',
                                        'accepted' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusNames = [
                                        'draft' => 'Черновик',
                                        'submitted' => 'На проверке',
                                        'needs_fix' => 'Доработка',
                                        'accepted' => 'Принято',
                                        'rejected' => 'Отклонено'
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$submission->status] }}">
                                {{ $statusNames[$submission->status] }}
                            </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $submission->created_at->format('d.m.Y H:i') }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Последние пользователи и конкурсы -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Последние пользователи -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-users mr-2 text-indigo-500"></i>
                        Новые пользователи
                    </h3>
                    <a href="{{ route('users.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                        Все пользователи
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($recent_users as $user)
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-800">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full
                        @if($user->role == 'admin') bg-purple-100 text-purple-800
                        @elseif($user->role == 'jury') bg-blue-100 text-blue-800
                        @else bg-green-100 text-green-800
                        @endif">
                        {{ $user->role }}
                    </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Последние конкурсы -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-trophy mr-2 text-yellow-500"></i>
                        Активные конкурсы
                    </h3>
                    <a href="{{ route('contests.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                        Все конкурсы
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($contests as $contest)
                        <div class="p-3 border border-gray-100 rounded-lg hover:shadow-sm transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-800">{{ $contest->title }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">Работ: {{ $contest->submissions_count }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs {{ $contest->deadline_at->isFuture() ? 'text-green-600' : 'text-red-600' }}">
                                        <i class="far fa-clock mr-1"></i>
                                        до {{ $contest->deadline_at->format('d.m.Y') }}
                                    </div>
                                    @if($contest->is_active)
                                        <span class="text-xs text-green-600 mt-1 inline-block">Активен</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
