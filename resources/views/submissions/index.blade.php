@extends('layouts.app')

@section('title', 'Все работы')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-tasks mr-2 text-indigo-500"></i>
                        Все работы
                    </h1>
                    <p class="text-gray-600 mt-1">Просмотр и управление всеми работами участников</p>
                </div>
                <div class="flex space-x-2">
                    <!-- Кнопка экспорта -->
                    <a href="{{ route('submissions.export', request()->all()) }}"
                       class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                        <i class="fas fa-download mr-2"></i>
                        Экспорт
                    </a>

                    <!-- Кнопка печати -->
                    <button onclick="window.print()"
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-print mr-2"></i>
                        Печать
                    </button>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="GET" action="{{ route('submissions.index') }}" class="grid md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Название работы..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                    <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Все статусы</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Конкурс</label>
                    <select name="contest_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Все конкурсы</option>
                        @foreach($contests as $contest)
                            <option value="{{ $contest->id }}" {{ request('contest_id') == $contest->id ? 'selected' : '' }}>
                                {{ $contest->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Участник</label>
                    <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Все участники</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex-1">
                        <i class="fas fa-search mr-2"></i>
                        Поиск
                    </button>
                    <a href="{{ route('submissions.index') }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Список работ -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            @if($submissions->isEmpty())
                <div class="text-center py-16">
                    <i class="fas fa-file-alt text-5xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Работы не найдены</h3>
                    <p class="text-gray-500">Попробуйте изменить параметры поиска</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('submissions.index', array_merge(request()->all(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                               class="hover:text-indigo-600">
                                ID
                                @if(request('sort') == 'id')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Работа</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участник</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Конкурс</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('submissions.index', array_merge(request()->all(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                               class="hover:text-indigo-600">
                                Статус
                                @if(request('sort') == 'status')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Файлы</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('submissions.index', array_merge(request()->all(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                               class="hover:text-indigo-600">
                                Дата
                                @if(request('sort') == 'created_at')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($submissions as $submission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                #{{ $submission->id }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $submission->title }}</div>
                                <div class="text-xs text-gray-500">{{ Str::limit($submission->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600">{{ $submission->contest->title }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="fas fa-paperclip mr-1"></i>
                                {{ $submission->attachments_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $submission->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('submissions.show', $submission) }}"
                                   class="text-indigo-600 hover:text-indigo-900 mr-3"
                                   title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                                    <a href="{{ route('submissions.show', $submission) }}#review"
                                       class="text-green-600 hover:text-green-900 mr-3"
                                       title="Оценить">
                                        <i class="fas fa-star"></i>
                                    </a>
                                @endif
                                @if($submission->status == 'submitted' && (auth()->user()->isJury() || auth()->user()->isAdmin()))
                                    <button onclick="quickAction({{ $submission->id }}, 'accept')"
                                            class="text-green-600 hover:text-green-900 mr-3"
                                            title="Быстро принять">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="quickAction({{ $submission->id }}, 'reject')"
                                            class="text-red-600 hover:text-red-900"
                                            title="Быстро отклонить">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <!-- Пагинация -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $submissions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Модальное окно для быстрых действий -->
    <div id="quickActionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Подтверждение действия</h3>
                <form id="quickActionForm" method="POST">
                    @csrf
                    <input type="hidden" name="status" id="actionStatus">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий (необязательно)</label>
                        <textarea name="comment" rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                  placeholder="Добавьте комментарий к решению..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Отмена
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Подтвердить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function quickAction(submissionId, action) {
            const modal = document.getElementById('quickActionModal');
            const form = document.getElementById('quickActionForm');
            const title = document.getElementById('modalTitle');

            if (action === 'accept') {
                title.textContent = 'Принять работу';
                form.action = `/submissions/${submissionId}/change-status`;
                document.getElementById('actionStatus').value = 'accepted';
            } else if (action === 'reject') {
                title.textContent = 'Отклонить работу';
                form.action = `/submissions/${submissionId}/change-status`;
                document.getElementById('actionStatus').value = 'rejected';
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('quickActionModal').classList.add('hidden');
        }

        // Закрытие по клику вне модального окна
        window.onclick = function(event) {
            const modal = document.getElementById('quickActionModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
@endpush
