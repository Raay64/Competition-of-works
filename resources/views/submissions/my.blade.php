@extends('layouts.app')

@section('title', 'Мои работы')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-file-alt mr-2 text-indigo-500"></i>
                        Мои работы
                    </h1>
                    <p class="text-gray-600 mt-1">Управление вашими работами и отслеживание статусов</p>
                </div>
                <a href="{{ route('submissions.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-plus mr-2"></i>
                    Новая работа
                </a>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @php
                $stats = [
                    'total' => $submissions->count(),
                    'draft' => $submissions->where('status', 'draft')->count(),
                    'submitted' => $submissions->where('status', 'submitted')->count(),
                    'needs_fix' => $submissions->where('status', 'needs_fix')->count(),
                    'accepted' => $submissions->where('status', 'accepted')->count(),
                    'rejected' => $submissions->where('status', 'rejected')->count(),
                ];
            @endphp

            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                <div class="text-xs text-gray-500">Всего работ</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-gray-800">{{ $stats['draft'] }}</div>
                <div class="text-xs text-gray-500">Черновики</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['submitted'] }}</div>
                <div class="text-xs text-gray-500">На проверке</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['needs_fix'] }}</div>
                <div class="text-xs text-gray-500">Требуют доработки</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['accepted'] }}</div>
                <div class="text-xs text-gray-500">Принято</div>
            </div>
        </div>

        <!-- Список работ -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            @if($submissions->isEmpty())
                <div class="text-center py-16">
                    <i class="fas fa-folder-open text-5xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">У вас пока нет работ</h3>
                    <p class="text-gray-500 mb-6">Создайте свою первую работу и участвуйте в конкурсах</p>
                    <a href="{{ route('submissions.create') }}"
                       class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>
                        Создать работу
                    </a>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Конкурс</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название работы</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Файлы</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата подачи</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($submissions as $submission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('contests.show', $submission->contest) }}"
                                   class="text-sm text-indigo-600 hover:text-indigo-900">
                                    {{ $submission->contest->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $submission->title }}</div>
                                <div class="text-xs text-gray-500">{{ Str::limit($submission->description, 50) }}</div>
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

                                @if(in_array($submission->status, ['draft', 'needs_fix']))
                                    <a href="{{ route('submissions.edit', $submission) }}"
                                       class="text-yellow-600 hover:text-yellow-900 mr-3"
                                       title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @if($submission->status == 'draft' && $submission->hasScannedAttachments())
                                    <form action="{{ route('submissions.submit', $submission) }}"
                                          method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-green-600 hover:text-green-900"
                                                title="Отправить на проверку">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif

                                @if($submission->status == 'needs_fix')
                                    <span class="text-orange-500" title="Требуются правки">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <!-- Пагинация -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
