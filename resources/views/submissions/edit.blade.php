@extends('layouts.app')

@section('title', 'Редактирование работы')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-edit mr-2 text-indigo-500"></i>
                    Редактирование работы
                </h2>
            </div>

            <!-- Форма -->
            <form action="{{ route('submissions.update', $submission) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <!-- Конкурс (только для чтения) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Конкурс
                    </label>
                    <input type="text"
                           value="{{ $submission->contest->title }}"
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg"
                           readonly>
                </div>

                <!-- Название работы -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Название работы *
                    </label>
                    <input type="text" name="title" id="title"
                           value="{{ old('title', $submission->title) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('title') border-red-500 @enderror"
                           required>
                    @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Описание -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание работы
                    </label>
                    <textarea name="description" id="description" rows="6"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $submission->description) }}</textarea>
                    @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Статус (информация) -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <span class="text-sm font-medium text-gray-700 mr-2">Текущий статус:</span>
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
                    </div>
                    @if($submission->status == 'needs_fix')
                        <p class="text-sm text-orange-600 mt-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Работа требует доработки. Пожалуйста, внесите необходимые изменения.
                        </p>
                    @endif
                </div>

                <!-- Информация о файлах -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Информация о файлах
                    </h3>
                    <p class="text-sm text-blue-700">
                        Загружено файлов: {{ $submission->attachments->count() }}/3<br>
                        Файлы можно загрузить/удалить на странице просмотра работы.
                    </p>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('submissions.show', $submission) }}"
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Отмена
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-save mr-2"></i>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>

        <!-- Подсказка для статуса needs_fix -->
        @if($submission->status == 'needs_fix')
            <div class="mt-6 bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-orange-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-orange-700">
                            <strong>Внимание:</strong> После внесения изменений не забудьте отправить работу на повторную проверку.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
