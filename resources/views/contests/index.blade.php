@extends('layouts.app')

@section('title', 'Конкурсы')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-trophy mr-2 text-yellow-500"></i>
                        Конкурсы
                    </h1>
                    <p class="text-gray-600 mt-1">Управление конкурсами и просмотр активных мероприятий</p>
                </div>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('contests.create') }}"
                       class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Создать конкурс
                    </a>
                @endif
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="GET" action="{{ route('contests.index') }}" class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Название конкурса..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                    <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Все статусы</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Дедлайн</label>
                    <select name="deadline" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Все</option>
                        <option value="upcoming" {{ request('deadline') == 'upcoming' ? 'selected' : '' }}>Предстоящие</option>
                        <option value="passed" {{ request('deadline') == 'passed' ? 'selected' : '' }}>Прошедшие</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex-1">
                        <i class="fas fa-search mr-2"></i>
                        Применить
                    </button>
                    <a href="{{ route('contests.index') }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Список конкурсов -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($contests as $contest)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                    <!-- Статус и дата -->
                    <div class="px-6 py-3 {{ $contest->is_active ? 'bg-green-500' : 'bg-gray-400' }} text-white flex justify-between items-center">
                <span class="text-sm font-medium">
                    <i class="fas {{ $contest->is_active ? 'fa-check-circle' : 'fa-pause-circle' }} mr-1"></i>
                    {{ $contest->is_active ? 'Активен' : 'Неактивен' }}
                </span>
                        <span class="text-sm">
                    <i class="far fa-calendar mr-1"></i>
                    до {{ $contest->deadline_at->format('d.m.Y') }}
                </span>
                    </div>

                    <!-- Контент -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">
                            <a href="{{ route('contests.show', $contest) }}" class="hover:text-indigo-600">
                                {{ $contest->title }}
                            </a>
                        </h3>

                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                            {{ $contest->description ?? 'Нет описания' }}
                        </p>

                        <!-- Статистика -->
                        <div class="grid grid-cols-3 gap-2 mb-4 text-center">
                            <div class="bg-gray-50 rounded p-2">
                                <div class="text-lg font-bold text-indigo-600">{{ $contest->submissions_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Всего работ</div>
                            </div>
                            <div class="bg-gray-50 rounded p-2">
                                <div class="text-lg font-bold text-yellow-600">
                                    {{ $contest->submissions()->where('status', 'submitted')->count() }}
                                </div>
                                <div class="text-xs text-gray-500">На проверке</div>
                            </div>
                            <div class="bg-gray-50 rounded p-2">
                                <div class="text-lg font-bold text-green-600">
                                    {{ $contest->submissions()->where('status', 'accepted')->count() }}
                                </div>
                                <div class="text-xs text-gray-500">Принято</div>
                            </div>
                        </div>

                        <!-- Прогресс бар времени -->
                        @php
                            $totalDays = $contest->created_at->diffInDays($contest->deadline_at);
                            $daysLeft = now()->diffInDays($contest->deadline_at, false);
                            $progress = $totalDays > 0 ? (($totalDays - max(0, $daysLeft)) / $totalDays) * 100 : 0;
                        @endphp

                        <div class="mb-4">
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Прогресс конкурса</span>
                                <span>{{ round($progress) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>

                        <!-- Действия -->
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                <i class="far fa-clock mr-1"></i>
                                @if($contest->deadline_at->isFuture())
                                    Осталось {{ $contest->deadline_at->diffForHumans() }}
                                @else
                                    <span class="text-red-500">Завершен</span>
                                @endif
                            </div>

                            <div class="flex space-x-2">
                                <a href="{{ route('contests.show', $contest) }}"
                                   class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition"
                                   title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('contests.edit', $contest) }}"
                                       class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded-lg transition"
                                       title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($contest->submissions_count == 0)
                                        <form action="{{ route('contests.destroy', $contest) }}" method="POST"
                                              onsubmit="return confirm('Вы уверены, что хотите удалить этот конкурс?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition"
                                                    title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-lg shadow-lg p-12 text-center">
                    <i class="fas fa-trophy text-5xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Конкурсы не найдены</h3>
                    <p class="text-gray-500 mb-6">
                        @if(auth()->user()->isAdmin())
                            Создайте первый конкурс, чтобы начать работу
                        @else
                            На данный момент нет активных конкурсов
                        @endif
                    </p>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('contests.create') }}"
                           class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-plus mr-2"></i>
                            Создать конкурс
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Пагинация -->
        <div class="mt-6">
            {{ $contests->links() }}
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endpush
