@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="space-y-6">
        <!-- Профиль пользователя -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Шапка профиля -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-8">
                <div class="flex items-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">
                    <span class="text-3xl font-bold text-indigo-600">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    </div>
                    <div class="ml-6 text-white">
                        <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                        <p class="text-indigo-100">{{ $user->email }}</p>
                        <div class="mt-2">
                            @php
                                $roleColors = [
                                    'admin' => 'bg-purple-200 text-purple-800',
                                    'jury' => 'bg-blue-200 text-blue-800',
                                    'participant' => 'bg-green-200 text-green-800'
                                ];
                                $roleNames = [
                                    'admin' => 'Администратор',
                                    'jury' => 'Член жюри',
                                    'participant' => 'Участник'
                                ];
                            @endphp
                            <span class="px-3 py-1 text-sm font-medium rounded-full {{ $roleColors[$user->role] }}">
                            {{ $roleNames[$user->role] }}
                        </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Информация -->
            <div class="p-6">
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-500 mb-1">Дата регистрации</div>
                        <div class="text-lg font-semibold text-gray-800">
                            {{ $user->created_at->format('d.m.Y') }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $user->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-500 mb-1">Последний вход</div>
                        <div class="text-lg font-semibold text-gray-800">
                            {{ $user->last_login_at ? $user->last_login_at->format('d.m.Y H:i') : 'Нет данных' }}
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-500 mb-1">Всего работ</div>
                        <div class="text-lg font-semibold text-gray-800">
                            {{ $stats['total_submissions'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика работ (для участников) -->
        @if($user->isParticipant())
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                    Статистика работ
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $stats['total_submissions'] }}</div>
                        <div class="text-sm text-gray-500">Всего</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $stats['draft'] }}</div>
                        <div class="text-sm text-gray-500">Черновики</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $stats['submitted'] }}</div>
                        <div class="text-sm text-gray-500">На проверке</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $stats['needs_fix'] }}</div>
                        <div class="text-sm text-gray-500">Доработка</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $stats['accepted'] }}</div>
                        <div class="text-sm text-gray-500">Принято</div>
                    </div>
                </div>

                <!-- График прогресса -->
                @php
                    $total = $stats['total_submissions'] ?: 1;
                @endphp
                <div class="mt-4">
                    <div class="flex h-3 rounded-full overflow-hidden">
                        <div style="width: {{ ($stats['accepted'] / $total) * 100 }}%" class="bg-green-500"></div>
                        <div style="width: {{ ($stats['rejected'] / $total) * 100 }}%" class="bg-red-500"></div>
                        <div style="width: {{ ($stats['submitted'] / $total) * 100 }}%" class="bg-yellow-500"></div>
                        <div style="width: {{ ($stats['needs_fix'] / $total) * 100 }}%" class="bg-orange-500"></div>
                        <div style="width: {{ ($stats['draft'] / $total) * 100 }}%" class="bg-gray-500"></div>
                    </div>
                </div>
            </div>

            <!-- Список работ -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-list mr-2 text-indigo-500"></i>
                    Работы участника
                </h2>

                @if($submissions->isEmpty())
                    <p class="text-gray-500 text-center py-4">У пользователя пока нет работ</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Конкурс</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Файлы</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($submissions as $submission)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <a href="{{ route('contests.show', $submission->contest) }}" class="text-indigo-600 hover:text-indigo-800">
                                            {{ $submission->contest->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-900">{{ $submission->title }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'submitted' => 'bg-yellow-100 text-yellow-800',
                                                'needs_fix' => 'bg-orange-100 text-orange-800',
                                                'accepted' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800'
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$submission->status] }}">
                                    {{ $submission->status }}
                                </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $submission->attachments_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $submission->created_at->format('d.m.Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('submissions.show', $submission) }}"
                                           class="text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="mt-4">
                        {{ $submissions->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Кнопки действий -->
        <div class="flex justify-end space-x-3">
            @if(auth()->user()->isAdmin() || auth()->id() === $user->id)
                <a href="{{ route('users.edit', $user) }}"
                   class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>
                    Редактировать профиль
                </a>
            @endif

            @if(auth()->user()->isAdmin() && auth()->id() !== $user->id)
                <button onclick="changeRole({{ $user->id }}, '{{ $user->role }}')"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Сменить роль
                </button>

                @if($stats['total_submissions'] == 0)
                    <form action="{{ route('users.destroy', $user) }}"
                          method="POST"
                          onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                            <i class="fas fa-trash mr-2"></i>
                            Удалить пользователя
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
@endsection
