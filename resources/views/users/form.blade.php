@extends('layouts.app')

@section('title', isset($user) ? 'Редактирование пользователя' : 'Создание пользователя')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas {{ isset($user) ? 'fa-edit' : 'fa-user-plus' }} mr-2 text-indigo-500"></i>
                    {{ isset($user) ? 'Редактирование пользователя' : 'Создание нового пользователя' }}
                </h2>
            </div>

            <!-- Форма -->
            <form action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}"
                  method="POST" class="p-6">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <!-- Имя -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1 text-indigo-500"></i>
                        Имя *
                    </label>
                    <input type="text" name="name" id="name"
                           value="{{ old('name', $user->name ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="Введите полное имя"
                           required>
                    @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1 text-indigo-500"></i>
                        Email *
                    </label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email', $user->email ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror"
                           placeholder="user@example.com"
                           required>
                    @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Пароль -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1 text-indigo-500"></i>
                        {{ isset($user) ? 'Новый пароль (оставьте пустым, чтобы не менять)' : 'Пароль *' }}
                    </label>
                    <input type="password" name="password" id="password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror"
                           placeholder="{{ isset($user) ? 'Введите новый пароль' : 'Минимум 8 символов' }}"
                        {{ !isset($user) ? 'required' : '' }}>
                    @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Подтверждение пароля -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1 text-indigo-500"></i>
                        Подтверждение пароля
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Введите пароль еще раз"
                        {{ !isset($user) ? 'required' : '' }}>
                </div>

                <!-- Роль (только для админа) -->
                @if(auth()->user()->isAdmin())
                    <div class="mb-6">
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-tag mr-1 text-indigo-500"></i>
                            Роль *
                        </label>
                        <select name="role" id="role"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('role') border-red-500 @enderror"
                                required>
                            <option value="">Выберите роль</option>
                            <option value="participant" {{ old('role', $user->role ?? '') == 'participant' ? 'selected' : '' }}>Участник</option>
                            <option value="jury" {{ old('role', $user->role ?? '') == 'jury' ? 'selected' : '' }}>Член жюри</option>
                            <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Администратор</option>
                        </select>
                        @error('role')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Дополнительные настройки (только для админа) -->
                @if(auth()->user()->isAdmin() && isset($user))
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">
                            <i class="fas fa-cog mr-1 text-indigo-500"></i>
                            Дополнительные настройки
                        </h3>

                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="email_verified" value="1"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    {{ $user->email_verified_at ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600">
                            Email подтвержден
                        </span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="is_blocked" value="1"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    {{ $user->is_blocked ?? false ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600">
                            Заблокировать пользователя
                        </span>
                            </label>
                        </div>
                    </div>
                @endif

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('users.index') }}"
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Отмена
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas {{ isset($user) ? 'fa-save' : 'fa-user-plus' }} mr-2"></i>
                        {{ isset($user) ? 'Сохранить изменения' : 'Создать пользователя' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Подсказки -->
        @if(!isset($user))
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Подсказка:</strong> После создания пользователь получит доступ к системе
                            и сможет участвовать в конкурсах в соответствии с выбранной ролью.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
