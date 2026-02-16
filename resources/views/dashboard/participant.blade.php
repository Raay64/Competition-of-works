@extends('layouts.app')

@section('title', 'Панель участника')

@section('content')
    <div class="space-y-6">
        <!-- Приветствие -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Добро пожаловать, {{ auth()->user()->name }}!
            </h1>
            <p class="text-gray-600 mt-2">
                Здесь вы можете управлять своими работами и отслеживать их статус.
            </p>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-gray-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего работ</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_submissions'] }}</p>
                    </div>
                    <i class="fas fa-file-alt text-4xl text-gray-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Черновики</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['draft'] }}</p>
                    </div>
                    <i class="fas fa-pencil-alt text-4xl text-yellow-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">На проверке</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['submitted'] + $stats['needs_fix'] }}</p>
                    </div>
                    <i class="fas fa-clock text-4xl text-blue-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Завершено</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['accepted'] + $stats['rejected'] }}</p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-400"></i>
                </div>
            </div>
        </div>

        <!-- Активные конкурсы -->
        @if($active_contests->isNotEmpty())
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-fire text-orange-500 mr-2"></i>
                        Активные конкурсы
                    </h2>
                    <a href="{{ route('submissions.create') }}"
                       class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Новая работа
                    </a>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($active_contests as $contest)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <h3 class="font-semibold text-gray-800 mb-2">{{ $contest->title }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $contest->description }}</p>
                            <div class="flex items-center justify-between text-sm">
                    <span class="text-red-500">
                        <i class="far fa-clock mr-1"></i>
                        до {{ $contest->deadline_at->format('d.m.Y') }}
                    </span>
                                <a href="{{ route('submissions.create', ['contest_id' => $contest->id]) }}"
                                   class="text-indigo-600 hover:text-indigo-800">
                                    Подать работу <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Мои работы -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-list mr-2 text-indigo-500"></i>
                Мои работы
            </h2>

            @if($submissions->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-folder-open text-5xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">У вас пока нет работ</p>
                    <a href="{{ route('submissions.create') }}"
                       class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Создать первую работу
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Конкурс</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Файлы</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($submissions as $submission)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $submission->contest->title }}</div>
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
                                            'needs_fix' => 'Требуется доработка',
                                            'accepted' => 'Принято',
                                            'rejected' => 'Отклонено'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$submission->status] }}">
                                    {{ $statusNames[$submission->status] }}
                                </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">{{ $submission->attachments_count ?? 0 }} файлов</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $submission->created_at->format('d.m.Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('submissions.show', $submission) }}"
                                       class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($submission->status, ['draft', 'needs_fix']))
                                        <a href="{{ route('submissions.edit', $submission) }}"
                                           class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Последние комментарии -->
        @if(isset($recent_comments) && $recent_comments->isNotEmpty())
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-comments mr-2 text-green-500"></i>
                    Последние комментарии
                </h2>
                <div class="space-y-4">
                    @foreach($recent_comments as $comment)
                        <div class="border-l-4 border-indigo-400 bg-gray-50 p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm text-gray-800">{{ $comment->body }}</p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $comment->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <a href="{{ route('submissions.show', $comment->submission) }}"
                                   class="text-indigo-600 hover:text-indigo-800 text-sm">
                                    Перейти к работе <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endpush
