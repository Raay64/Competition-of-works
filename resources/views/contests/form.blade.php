@extends('layouts.app')

@section('title', isset($contest) ? 'Редактирование конкурса' : 'Создание конкурса')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas {{ isset($contest) ? 'fa-edit' : 'fa-plus' }} mr-2 text-indigo-500"></i>
                    {{ isset($contest) ? 'Редактирование конкурса' : 'Создание нового конкурса' }}
                </h2>
            </div>

            <!-- Форма -->
            <form action="{{ isset($contest) ? route('contests.update', $contest) : route('contests.store') }}"
                  method="POST" class="p-6">
                @csrf
                @if(isset($contest))
                    @method('PUT')
                @endif

                <!-- Название -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heading mr-1 text-indigo-500"></i>
                        Название конкурса *
                    </label>
                    <input type="text" name="title" id="title"
                           value="{{ old('title', $contest->title ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('title') border-red-500 @enderror"
                           placeholder="Введите название конкурса"
                           required>
                    @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Описание -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-1 text-indigo-500"></i>
                        Описание
                    </label>
                    <textarea name="description" id="description" rows="6"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                              placeholder="Подробное описание конкурса, требования к работам, критерии оценки...">{{ old('description', $contest->description ?? '') }}</textarea>
                    @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Дедлайн и статус -->
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="deadline_at" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1 text-indigo-500"></i>
                            Дата и время окончания *
                        </label>
                        <input type="datetime-local" name="deadline_at" id="deadline_at"
                               value="{{ old('deadline_at', isset($contest) ? $contest->deadline_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('deadline_at') border-red-500 @enderror"
                               required>
                        @error('deadline_at')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-toggle-on mr-1 text-indigo-500"></i>
                            Статус
                        </label>
                        <div class="flex items-center h-full">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       class="sr-only peer"
                                    {{ old('is_active', $contest->is_active ?? true) ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-700">
                                Активный конкурс
                            </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Дополнительные настройки (опционально) -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-cog mr-1 text-indigo-500"></i>
                        Дополнительные настройки
                    </h3>

                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="allow_multiple_submissions" value="1"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('allow_multiple_submissions', $contest->allow_multiple_submissions ?? false) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600">
                            Разрешить участникам подавать несколько работ
                        </span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="hide_results_until_deadline" value="1"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('hide_results_until_deadline', $contest->hide_results_until_deadline ?? false) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600">
                            Скрывать результаты до окончания конкурса
                        </span>
                        </label>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('contests.index') }}"
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Отмена
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas {{ isset($contest) ? 'fa-save' : 'fa-plus' }} mr-2"></i>
                        {{ isset($contest) ? 'Сохранить изменения' : 'Создать конкурс' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Подсказки -->
        @if(!isset($contest))
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Подсказка:</strong> После создания конкурса участники смогут подавать работы.
                            Не забудьте указать все требования к работам в описании.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Устанавливаем минимальную дату для дедлайна (сегодня)
        document.addEventListener('DOMContentLoaded', function() {
            const deadlineInput = document.getElementById('deadline_at');
            if (deadlineInput && !deadlineInput.value) {
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                deadlineInput.min = now.toISOString().slice(0, 16);
            }
        });
    </script>
@endpush
