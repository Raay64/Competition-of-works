@extends('layouts.app')

@section('title', 'Просмотр уведомления')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-bell mr-2 text-indigo-500"></i>
                        Просмотр уведомления
                    </h2>
                    <a href="{{ route('notifications.index') }}"
                       class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Назад к списку
                    </a>
                </div>
            </div>

            <!-- Содержимое уведомления -->
            <div class="p-6">
                <!-- Тип и время -->
                <div class="flex justify-between items-start mb-6">
                    <div>
                        @php
                            $typeNames = [
                                'status_changed' => 'Изменение статуса',
                                'new_comment' => 'Новый комментарий',
                                'deadline_approaching' => 'Приближение дедлайна',
                                'submission_received' => 'Получена работа',
                            ];
                            $typeIcons = [
                                'status_changed' => 'fa-exchange-alt text-blue-500',
                                'new_comment' => 'fa-comment text-green-500',
                                'deadline_approaching' => 'fa-clock text-yellow-500',
                                'submission_received' => 'fa-file-alt text-purple-500',
                            ];
                        @endphp
                        <div class="flex items-center">
                            <i class="fas {{ $typeIcons[$notification->type] ?? 'fa-bell text-gray-500' }} text-2xl mr-3"></i>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ $typeNames[$notification->type] ?? 'Уведомление' }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $notification->created_at->format('d.m.Y H:i') }}
                                    ({{ $notification->created_at->diffForHumans() }})
                                </p>
                            </div>
                        </div>
                    </div>

                    @if(!$notification->read_at)
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                        Новое
                    </span>
                    @endif
                </div>

                <!-- Сообщение -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-800">
                        {{ $data['message'] ?? 'Нет описания' }}
                    </p>
                </div>

                <!-- Детали уведомления -->
                <div class="space-y-4">
                    @if(isset($data['submission_title']))
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-file-alt text-indigo-500 w-6"></i>
                            <span class="text-sm text-gray-600">Работа:</span>
                            <a href="{{ route('submissions.show', $data['submission_id']) }}" class="ml-2 text-indigo-600 hover:text-indigo-800">
                                {{ $data['submission_title'] }}
                            </a>
                        </div>
                    @endif

                        @if(isset($data['contest_title']) && isset($data['contest_id']))
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <i class="fas fa-trophy text-yellow-500 w-6"></i>
                                <span class="text-sm text-gray-600">Конкурс:</span>
                                <a href="{{ route('contests.show', $data['contest_id']) }}" class="ml-2 text-indigo-600 hover:text-indigo-800">
                                    {{ $data['contest_title'] }}
                                </a>
                            </div>
                        @elseif(isset($data['contest_title']))
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <i class="fas fa-trophy text-yellow-500 w-6"></i>
                                <span class="text-sm text-gray-600">Конкурс:</span>
                                <span class="ml-2 font-medium">{{ $data['contest_title'] }}</span>
                            </div>
                        @endif

                    @if(isset($data['commenter_name']))
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-user text-green-500 w-6"></i>
                            <span class="text-sm text-gray-600">Автор комментария:</span>
                            <span class="ml-2 font-medium">{{ $data['commenter_name'] }}</span>
                            @if(isset($data['commenter_role']))
                                <span class="ml-2 text-xs px-2 py-1 rounded-full
                            @if($data['commenter_role'] == 'jury') bg-blue-100 text-blue-800
                            @elseif($data['commenter_role'] == 'admin') bg-purple-100 text-purple-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ $data['commenter_role'] }}
                        </span>
                            @endif
                        </div>
                    @endif

                    @if(isset($data['old_status']) && isset($data['new_status']))
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm text-gray-600">Было:</span>
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
                                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$data['old_status']] }}">
                                {{ $statusNames[$data['old_status']] }}
                            </span>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400"></i>
                                <div>
                                    <span class="text-sm text-gray-600">Стало:</span>
                                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$data['new_status']] }}">
                                {{ $statusNames[$data['new_status']] }}
                            </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($data['comment_body']))
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Текст комментария:</p>
                            <p class="text-gray-800">{{ $data['comment_body'] }}</p>
                        </div>
                    @endif
                </div>

                <!-- Кнопки действий -->
                <div class="mt-6 flex justify-end space-x-3">
                    @if(!$notification->read_at)
                        <form action="{{ route('notifications.read', $notification) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                <i class="fas fa-check mr-2"></i>
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
                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                            <i class="fas fa-trash mr-2"></i>
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
