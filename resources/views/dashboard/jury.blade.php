@extends('layouts.app')

@section('title', 'Панель жюри')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Панель жюри
            </h1>
            <p class="text-gray-600 mt-2">
                Здесь вы можете просматривать и оценивать работы участников.
            </p>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего работ</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_submissions'] }}</p>
                    </div>
                    <i class="fas fa-file-alt text-4xl text-gray-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Ожидают проверки</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_review'] }}</p>
                    </div>
                    <i class="fas fa-hourglass-half text-4xl text-yellow-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Требуют доработки</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $stats['needs_fix'] }}</p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-4xl text-orange-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Принято</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['accepted'] }}</p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-400"></i>
                </div>
            </div>
        </div>

        <!-- Работы на проверку -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-clipboard-list mr-2 text-indigo-500"></i>
                    Работы на проверку
                </h2>
                <div class="flex space-x-2">
                    <select id="contest-filter" class="rounded-md border-gray-300 text-sm">
                        <option value="">Все конкурсы</option>
                        @foreach($contests ?? [] as $contest)
                            <option value="{{ $contest->id }}">{{ $contest->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($submissions_to_review->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-check-circle text-5xl text-green-400 mb-4"></i>
                    <p class="text-gray-500">Нет работ, ожидающих проверки</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($submissions_to_review as $submission)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $submission->status == 'submitted' ? 'bg-yellow-100 text-yellow-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $submission->status == 'submitted' ? 'На проверке' : 'Требует доработки' }}
                                </span>
                                        <span class="text-sm text-gray-500">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ $submission->created_at->diffForHumans() }}
                                </span>
                                    </div>

                                    <h3 class="font-semibold text-lg text-gray-800 mb-1">
                                        <a href="{{ route('submissions.show', $submission) }}" class="hover:text-indigo-600">
                                            {{ $submission->title }}
                                        </a>
                                    </h3>

                                    <p class="text-sm text-gray-600 mb-2">
                                        Конкурс: {{ $submission->contest->title }}
                                    </p>

                                    <div class="flex items-center space-x-4 text-sm">
                                <span class="text-gray-500">
                                    <i class="fas fa-user mr-1"></i>
                                    {{ $submission->user->name }}
                                </span>
                                        <span class="text-gray-500">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    {{ $submission->attachments->count() }} файлов
                                </span>
                                        @if($submission->comments->count() > 0)
                                            <span class="text-gray-500">
                                        <i class="fas fa-comments mr-1"></i>
                                        {{ $submission->comments->count() }} комментариев
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="ml-4 flex flex-col space-y-2">
                                    <a href="{{ route('submissions.show', $submission) }}"
                                       class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition text-center">
                                        <i class="fas fa-eye mr-1"></i>
                                        Проверить
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Последние решения -->
        @if(isset($recent_decisions) && $recent_decisions->isNotEmpty())
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-history mr-2 text-gray-500"></i>
                    Последние решения
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Работа</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Участник</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Решение</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recent_decisions as $submission)
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('submissions.show', $submission) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $submission->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $submission->user->name }}</td>
                                <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $submission->status == 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $submission->status == 'accepted' ? 'Принято' : 'Отклонено' }}
                            </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $submission->updated_at->format('d.m.Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('contest-filter')?.addEventListener('change', function() {
            // Реализация фильтрации через AJAX
            const contestId = this.value;
            // TODO: добавить AJAX запрос для фильтрации
        });
    </script>
@endpush
