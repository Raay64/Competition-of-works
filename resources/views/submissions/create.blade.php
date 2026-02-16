@extends('layouts.app')

@section('title', 'Создание работы')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-plus-circle mr-2 text-indigo-500"></i>
                    Создание новой работы
                </h2>
            </div>

            <!-- Форма -->
            <form action="{{ route('submissions.store') }}" method="POST" class="p-6">
                @csrf

                <!-- Конкурс -->
                <div class="mb-6">
                    <label for="contest_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Конкурс *
                    </label>
                    <select name="contest_id" id="contest_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contest_id') border-red-500 @enderror"
                            required>
                        <option value="">Выберите конкурс</option>
                        @foreach($contests as $contest)
                            <option value="{{ $contest->id }}" {{ old('contest_id') == $contest->id ? 'selected' : '' }}>
                                {{ $contest->title }} (до {{ $contest->deadline_at->format('d.m.Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('contest_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Название работы -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Название работы *
                    </label>
                    <input type="text" name="title" id="title"
                           value="{{ old('title') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('title') border-red-500 @enderror"
                           placeholder="Введите название работы"
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
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                              placeholder="Опишите вашу работу...">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('submissions.index') }}"
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Отмена
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-save mr-2"></i>
                        Создать черновик
                    </button>
                </div>
            </form>
        </div>

        <!-- Подсказка -->
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Подсказка:</strong> После создания черновика вы сможете загрузить файлы работы.
                        Максимум 3 файла, размер каждого до 10MB. Разрешенные форматы: PDF, ZIP, PNG, JPG.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
