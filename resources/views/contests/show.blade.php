@extends('layouts.app')

@section('title', $contest->title)

@section('content')
    <div class="space-y-6">
        <!-- Заголовок с действиями -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <div class="flex items-center space-x-3">
                        <h1 class="text-2xl font-bold text-gray-800">{{ $contest->title }}</h1>
                        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $contest->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $contest->is_active ? 'Активен' : 'Неактивен' }}
                    </span>
                    </div>
                    <p class="text-gray-600 mt-2">{{ $contest->description ?? 'Нет описания' }}</p>
                </div>

                <div class="flex space-x-2 mt-4 md:mt-0">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('contests.edit', $contest) }}"
                           class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                            <i class="fas fa-edit mr-2"></i>
                            Редактировать
                        </a>
                    @endif

                    @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                        <a href="{{ route('contests.export', $contest) }}"
                           class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                            <i class="fas fa-download mr-2"></i>
                            Экспорт работ
                        </a>
                    @endif

                    @if(auth()->user()->isParticipant() && $contest->is_active && $contest->deadline_at->isFuture())
                        <a href="{{ route('submissions.create', ['contest_id' => $contest->id]) }}"
                           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-plus mr-2"></i>
                            Подать работу
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Информация о конкурсе -->
        <div class="grid md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-sm text-gray-500 mb-1">Дата начала</div>
                <div class="text-lg font-bold text-gray-800">{{ $contest->created_at->format('d.m.Y') }}</div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-sm text-gray-500 mb-1">Дедлайн</div>
                <div class="text-lg font-bold {{ $contest->deadline_at->isPast() ? 'text-red-600' : 'text-green-600' }}">
                    {{ $contest->deadline_at->format('d.m.Y H:i') }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    @if($contest->deadline_at->isFuture())
                        Осталось {{ $contest->deadline_at->diffForHumans() }}
                    @else
                        Завершен
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-sm text-gray-500 mb-1">Всего работ</div>
                <div class="text-lg font-bold text-gray-800">{{ $stats['total_submissions'] }}</div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-sm text-gray-500 mb-1">Принято работ</div>
                <div class="text-lg font-bold text-green-600">{{ $stats['submissions_by_status']['accepted'] }}</div>
            </div>
        </div>

        <!-- Статистика по статусам -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                Статистика по статусам
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @foreach([
                    'draft' => ['label' => 'Черновики', 'color' => 'gray', 'icon' => 'fa-pencil-alt'],
                    'submitted' => ['label' => 'На проверке', 'color' => 'yellow', 'icon' => 'fa-clock'],
                    'needs_fix' => ['label' => 'Доработка', 'color' => 'orange', 'icon' => 'fa-exclamation-triangle'],
                    'accepted' => ['label' => 'Принято', 'color' => 'green', 'icon' => 'fa-check-circle'],
                    'rejected' => ['label' => 'Отклонено', 'color' => 'red', 'icon' => 'fa-times-circle']
                ] as $status => $info)
                    <div class="bg-{{ $info['color'] }}-50 rounded-lg p-4 text-center">
                        <i class="fas {{ $info['icon'] }} text-{{ $info['color'] }}-500 text-2xl mb-2"></i>
                        <div class="text-2xl font-bold text-{{ $info['color'] }}-600">{{ $stats['submissions_by_status'][$status] }}</div>
                        <div class="text-sm text-gray-600">{{ $info['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <!-- График прогресса -->
            @php
                $total = $stats['total_submissions'] ?: 1;
            @endphp
            <div class="mt-6">
                <div class="flex h-4 rounded-full overflow-hidden">
                    <div style="width: {{ ($stats['submissions_by_status']['accepted'] / $total) * 100 }}%"
                         class="bg-green-500"></div>
                    <div style="width: {{ ($stats['submissions_by_status']['rejected'] / $total) * 100 }}%"
                         class="bg-red-500"></div>
                    <div style="width: {{ ($stats['submissions_by_status']['submitted'] / $total) * 100 }}%"
                         class="bg-yellow-500"></div>
                    <div style="width: {{ ($stats['submissions_by_status']['needs_fix'] / $total) * 100 }}%"
                         class="bg-orange-500"></div>
                    <div style="width: {{ ($stats['submissions_by_status']['draft'] / $total) * 100 }}%"
                         class="bg-gray-500"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-600 mt-2">
                    <span>Принято: {{ round(($stats['submissions_by_status']['accepted'] / $total) * 100) }}%</span>
                    <span>Отклонено: {{ round(($stats['submissions_by_status']['rejected'] / $total) * 100) }}%</span>
                    <span>На проверке: {{ round(($stats['submissions_by_status']['submitted'] / $total) * 100) }}%</span>
                    <span>Доработка: {{ round(($stats['submissions_by_status']['needs_fix'] / $total) * 100) }}%</span>
                    <span>Черновики: {{ round(($stats['submissions_by_status']['draft'] / $total) * 100) }}%</span>
                </div>
            </div>
        </div>

        <!-- Список работ -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-list mr-2 text-indigo-500"></i>
                Работы участников
            </h2>

            @if($submissions->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-file-alt text-5xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">В этом конкурсе пока нет работ</p>
                    @if(auth()->user()->isParticipant() && $contest->is_active && $contest->deadline_at->isFuture())
                        <a href="{{ route('submissions.create', ['contest_id' => $contest->id]) }}"
                           class="inline-block mt-4 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-plus mr-2"></i>
                            Стать первым участником
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участник</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название работы</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Файлы</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата подачи</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($submissions as $submission)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-indigo-600 text-sm font-medium">
                                            {{ substr($submission->user->name, 0, 1) }}
                                        </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $submission->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $submission->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $submission->title }}</div>
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
                                    <i class="fas fa-paperclip mr-1"></i>
                                    {{ $submission->attachments_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $submission->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('submissions.show', $submission) }}"
                                       class="text-indigo-600 hover:text-indigo-900 mr-3"
                                       title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                                        <a href="{{ route('submissions.show', $submission) }}#review"
                                           class="text-green-600 hover:text-green-900"
                                           title="Оценить">
                                            <i class="fas fa-star"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div class="mt-6">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
