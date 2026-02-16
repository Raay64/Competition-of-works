@extends('layouts.app')

@section('title', 'Настройки уведомлений')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-cog mr-2 text-indigo-500"></i>
                    Настройки уведомлений
                </h2>
            </div>

            <!-- Форма настроек -->
            <form action="{{ route('notifications.update-settings') }}" method="POST" class="p-6">
                @csrf

                <div class="space-y-6">
                    <!-- Email уведомления -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Email уведомления</h3>

                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Изменение статуса работы</span>
                                    <p class="text-xs text-gray-500">Получать уведомления при изменении статуса ваших работ</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                    <input type="checkbox" name="email_on_status_change" id="email_on_status_change"
                                           class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                        {{ $settings['email_on_status_change'] ? 'checked' : '' }}>
                                    <label for="email_on_status_change" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </label>

                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Новые комментарии</span>
                                    <p class="text-xs text-gray-500">Получать уведомления о новых комментариях к вашим работам</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                    <input type="checkbox" name="email_on_comments" id="email_on_comments"
                                           class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                        {{ $settings['email_on_comments'] ? 'checked' : '' }}>
                                    <label for="email_on_comments" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </label>

                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Приближение дедлайна</span>
                                    <p class="text-xs text-gray-500">Напоминания о приближающихся сроках сдачи работ</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                    <input type="checkbox" name="email_on_deadline" id="email_on_deadline"
                                           class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                        {{ $settings['email_on_deadline'] ? 'checked' : '' }}>
                                    <label for="email_on_deadline" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Push уведомления (в браузере) -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Push уведомления</h3>

                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Мгновенные уведомления</span>
                                    <p class="text-xs text-gray-500">Получать уведомления в браузере о важных событиях</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                    <input type="checkbox" name="push_notifications" id="push_notifications"
                                           class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                        {{ $settings['push_notifications'] ?? true ? 'checked' : '' }}>
                                    <label for="push_notifications" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Периодичность дайджеста -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Дайджест</h3>

                        <div class="p-3 bg-gray-50 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Периодичность email-дайджеста
                            </label>
                            <select name="digest_frequency" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="never" {{ ($settings['digest_frequency'] ?? 'daily') == 'never' ? 'selected' : '' }}>Никогда</option>
                                <option value="daily" {{ ($settings['digest_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Ежедневно</option>
                                <option value="weekly" {{ ($settings['digest_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>Еженедельно</option>
                                <option value="monthly" {{ ($settings['digest_frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>Ежемесячно</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Сводка всех событий за выбранный период
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('dashboard') }}"
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Отмена
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-save mr-2"></i>
                        Сохранить настройки
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .toggle-checkbox:checked {
            right: 0;
            border-color: #4f46e5;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #4f46e5;
        }
        .toggle-checkbox {
            right: 0;
            transition: all 0.3s;
        }
        .toggle-label {
            transition: all 0.3s;
        }
    </style>
@endsection
