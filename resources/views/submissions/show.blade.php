@extends('layouts.app')

@section('title', $submission->title)

@section('content')
    <div class="space-y-6">
        <!-- Заголовок с действиями -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-800">{{ $submission->title }}</h1>
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
                        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$submission->status] }}">
                        {{ $statusNames[$submission->status] }}
                    </span>
                    </div>
                    <p class="text-gray-600">
                        Конкурс: <a href="{{ route('contests.show', $submission->contest) }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ $submission->contest->title }}
                        </a>
                    </p>
                </div>

                <div class="flex space-x-2 mt-4 md:mt-0">
                    @if($can_edit)
                        <a href="{{ route('submissions.edit', $submission) }}"
                           class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                            <i class="fas fa-edit mr-2"></i>
                            Редактировать
                        </a>
                    @endif

                    @if($can_submit)
                        <form action="{{ route('submissions.submit', $submission) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Отправить на проверку
                            </button>
                        </form>
                    @endif

                    @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                        <button onclick="openStatusModal()"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Изменить статус
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Информация о работе -->
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Основная информация -->
            <div class="md:col-span-2 space-y-6">
                <!-- Описание -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-align-left mr-2 text-indigo-500"></i>
                        Описание работы
                    </h2>
                    <div class="prose max-w-none">
                        {{ $submission->description ?? 'Нет описания' }}
                    </div>
                </div>

                <!-- Файлы -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-paperclip mr-2 text-indigo-500"></i>
                        Прикрепленные файлы ({{ $attachments_stats['total'] }}/3)
                    </h2>

                    @if($attachments_stats['total'] > 0)
                        <div class="space-y-3">
                            @foreach($submission->attachments as $attachment)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-file text-2xl
                                    @if($attachment->mime == 'application/pdf') text-red-500
                                    @elseif($attachment->mime == 'application/zip') text-yellow-500
                                    @elseif(strpos($attachment->mime, 'image/') === 0) text-green-500
                                    @else text-gray-500
                                    @endif">
                                        </i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $attachment->original_name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ number_format($attachment->size / 1024, 1) }} KB •
                                                {{ $attachment->created_at->format('d.m.Y H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <!-- Статус сканирования -->
                                        @if($attachment->status == 'scanned')
                                            <span class="text-xs text-green-600">
                                        <i class="fas fa-check-circle"></i> Проверен
                                    </span>
                                        @elseif($attachment->status == 'rejected')
                                            <span class="text-xs text-red-600" title="{{ $attachment->rejection_reason }}">
                                        <i class="fas fa-exclamation-circle"></i> Отклонен
                                    </span>
                                        @else
                                            <span class="text-xs text-yellow-600">
                                        <i class="fas fa-clock"></i> Проверяется
                                    </span>
                                        @endif

                                        <!-- Скачивание -->
                                        <a href="{{ route('attachments.download', $attachment) }}"
                                           class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition"
                                           title="Скачать">
                                            <i class="fas fa-download"></i>
                                        </a>

                                        <!-- Удаление (только для черновиков) -->
                                        @if($can_edit)
                                            <form action="{{ route('attachments.destroy', $attachment) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Удалить файл?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition"
                                                        title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">Файлы не загружены</p>
                    @endif

                    <!-- Загрузка файлов -->
                    @if($can_upload)
                        <div class="mt-4 p-4 border-2 border-dashed border-gray-300 rounded-lg">
                            <form action="{{ route('attachments.upload', $submission) }}"
                                  method="POST"
                                  enctype="multipart/form-data"
                                  id="uploadForm">
                                @csrf
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600 mb-2">Перетащите файлы или нажмите для выбора</p>
                                    <input type="file"
                                           name="file"
                                           id="file"
                                           class="hidden"
                                           accept=".pdf,.zip,.png,.jpg,.jpeg"
                                           onchange="document.getElementById('uploadForm').submit()">
                                    <button type="button"
                                            onclick="document.getElementById('file').click()"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                        <i class="fas fa-upload mr-2"></i>
                                        Выбрать файл
                                    </button>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Максимальный размер: 10MB. Разрешены: PDF, ZIP, PNG, JPG
                                    </p>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Боковая панель -->
            <div class="space-y-6">
                <!-- Информация об участнике -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-user mr-2 text-indigo-500"></i>
                        Участник
                    </h2>
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-indigo-600 text-lg font-medium">
                            {{ substr($submission->user->name, 0, 1) }}
                        </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $submission->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $submission->user->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Статистика файлов -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                        Статистика файлов
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Проверено</span>
                                <span class="font-medium text-green-600">{{ $attachments_stats['scanned'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full"
                                     style="width: {{ $attachments_stats['total'] > 0 ? ($attachments_stats['scanned'] / $attachments_stats['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">В очереди</span>
                                <span class="font-medium text-yellow-600">{{ $attachments_stats['pending'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full"
                                     style="width: {{ $attachments_stats['total'] > 0 ? ($attachments_stats['pending'] / $attachments_stats['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Отклонено</span>
                                <span class="font-medium text-red-600">{{ $attachments_stats['rejected'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full"
                                     style="width: {{ $attachments_stats['total'] > 0 ? ($attachments_stats['rejected'] / $attachments_stats['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- История изменений -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-history mr-2 text-indigo-500"></i>
                        История
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500">Создано</p>
                            <p class="font-medium">{{ $submission->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Последнее обновление</p>
                            <p class="font-medium">{{ $submission->updated_at->format('d.m.Y H:i') }}</p>
                        </div>
                        @if($submission->status == 'submitted')
                            <div>
                                <p class="text-gray-500">Отправлено на проверку</p>
                                <p class="font-medium text-yellow-600">{{ $submission->updated_at->format('d.m.Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Комментарии -->
        <div class="bg-white rounded-lg shadow-lg p-6" id="comments">
            <h2 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-comments mr-2 text-indigo-500"></i>
                Комментарии ({{ $submission->comments->count() }})
            </h2>

            <!-- Список комментариев -->
            <div class="space-y-4 mb-6 max-h-96 overflow-y-auto" id="comments-list">
                @forelse($submission->comments as $comment)
                    <div class="flex space-x-3 {{ $comment->user_id == auth()->id() ? 'justify-end' : '' }}">
                        @if($comment->user_id != auth()->id())
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-indigo-600 text-sm font-medium">
                            {{ substr($comment->user->name, 0, 1) }}
                        </span>
                                </div>
                            </div>
                        @endif

                        <div class="flex-1 max-w-lg">
                            <div class="bg-gray-50 rounded-lg p-3 {{ $comment->user_id == auth()->id() ? 'bg-indigo-50' : '' }}">
                                <div class="flex justify-between items-start mb-1">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $comment->user->name }}
                                @if($comment->user->isJury())
                                    <span class="ml-1 text-xs text-indigo-600">(жюри)</span>
                                @endif
                            </span>
                                    <span class="text-xs text-gray-500">
                                {{ $comment->created_at->diffForHumans() }}
                            </span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $comment->body }}</p>

                                @if($comment->user_id == auth()->id() || auth()->user()->isAdmin())
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button onclick="editComment({{ $comment->id }})"
                                                class="text-xs text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('comments.destroy', $comment) }}"
                                              method="POST"
                                              onsubmit="return confirm('Удалить комментарий?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($comment->user_id == auth()->id())
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 text-sm font-medium">
                            {{ substr($comment->user->name, 0, 1) }}
                        </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">Нет комментариев</p>
                @endforelse
            </div>

            <!-- Форма добавления комментария -->
            <form action="{{ route('comments.store', $submission) }}" method="POST" class="mt-4">
                @csrf
                <div class="flex space-x-2">
                <textarea name="body" rows="2"
                          class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                          placeholder="Напишите комментарий..."
                          required></textarea>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 self-end">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- В конце файла, перед @, добавить модальное окно для жюри -->
    @if(auth()->user()->isJury() || auth()->user()->isAdmin())
        <!-- Модальное окно для изменения статуса -->
        <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Изменение статуса работы</h3>

                    <form action="{{ route('submissions.change-status', $submission) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Выберите новый статус
                            </label>
                            <select name="status" id="statusSelect"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    onchange="updateStatusHint()">
                                @foreach($available_statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ $status == $submission->getNextRecommendedStatus() ? 'selected' : '' }}>
                                        {{ $statusNames[$status] ?? $status }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Подсказки по статусам -->
                            <div id="statusHint" class="mt-2 text-sm text-gray-500">
                                @foreach($available_statuses as $status)
                                    <div class="status-hint hidden" data-status="{{ $status }}">
                                        @if($status == 'accepted')
                                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                            Работа полностью соответствует требованиям и может быть принята
                                        @elseif($status == 'rejected')
                                            <i class="fas fa-times-circle text-red-500 mr-1"></i>
                                            Работа не соответствует требованиям и не может быть принята
                                        @elseif($status == 'needs_fix')
                                            <i class="fas fa-exclamation-triangle text-orange-500 mr-1"></i>
                                            Работа требует доработок. Укажите в комментарии, что нужно исправить
                                        @elseif($status == 'submitted')
                                            <i class="fas fa-clock text-yellow-500 mr-1"></i>
                                            Отправить работу на повторную проверку
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Комментарий к решению
                            </label>
                            <textarea name="comment" rows="4"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                      placeholder="Объясните причину изменения статуса..."></textarea>
                        </div>

                        <!-- Предупреждение для статуса rejected -->
                        <div id="rejectWarning" class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 rounded hidden">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        <strong>Внимание!</strong> Отклонение работы - окончательное решение.
                                        Участник больше не сможет её доработать.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeStatusModal()"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                Отмена
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function openStatusModal() {
                document.getElementById('statusModal').classList.remove('hidden');
                updateStatusHint();
            }

            function closeStatusModal() {
                document.getElementById('statusModal').classList.add('hidden');
            }

            function updateStatusHint() {
                const select = document.getElementById('statusSelect');
                const selectedStatus = select.value;

                // Скрываем все подсказки
                document.querySelectorAll('.status-hint').forEach(el => {
                    el.classList.add('hidden');
                });

                // Показываем подсказку для выбранного статуса
                const hint = document.querySelector(`.status-hint[data-status="${selectedStatus}"]`);
                if (hint) {
                    hint.classList.remove('hidden');
                }

                // Показываем предупреждение для rejected
                const rejectWarning = document.getElementById('rejectWarning');
                if (selectedStatus === 'rejected') {
                    rejectWarning.classList.remove('hidden');
                } else {
                    rejectWarning.classList.add('hidden');
                }
            }

            // Закрытие по клику вне модального окна
            window.onclick = function(event) {
                const modal = document.getElementById('statusModal');
                if (event.target == modal) {
                    closeStatusModal();
                }
            }
        </script>
    @endif
@endsection
