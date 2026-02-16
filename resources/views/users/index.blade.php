@extends('layouts.app')

@section('title', 'Управление пользователями')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-users mr-2 text-indigo-500"></i>
                        Пользователи
                    </h1>
                    <p class="text-gray-600 mt-1">Управление пользователями системы и их ролями</p>
                </div>
                <a href="{{ route('users.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-user-plus mr-2"></i>
                    Добавить пользователя
                </a>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-500">Всего пользователей</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="text-2xl font-bold text-green-600">{{ $stats['participants'] }}</div>
                <div class="text-sm text-gray-500">Участников</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['jury'] }}</div>
                <div class="text-sm text-gray-500">Членов жюри</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['admins'] }}</div>
                <div class="text-sm text-gray-500">Администраторов</div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="GET" action="{{ route('users.index') }}" class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Имя или email..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Роль</label>
                    <select name="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Все роли</option>
                        <option value="participant" {{ request('role') == 'participant' ? 'selected' : '' }}>Участник</option>
                        <option value="jury" {{ request('role') == 'jury' ? 'selected' : '' }}>Жюри</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Администратор</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Сортировка</label>
                    <select name="sort" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>По дате регистрации</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>По имени</option>
                        <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>По email</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex-1">
                        <i class="fas fa-search mr-2"></i>
                        Применить
                    </button>
                    <a href="{{ route('users.index') }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Список пользователей -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роль</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Работ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата регистрации</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Последний вход</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mr-3">
                                <span class="text-white font-medium">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $roleColors = [
                                    'admin' => 'bg-purple-100 text-purple-800',
                                    'jury' => 'bg-blue-100 text-blue-800',
                                    'participant' => 'bg-green-100 text-green-800'
                                ];
                                $roleNames = [
                                    'admin' => 'Администратор',
                                    'jury' => 'Член жюри',
                                    'participant' => 'Участник'
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $roleColors[$user->role] }}">
                            {{ $roleNames[$user->role] }}
                        </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $user->submissions_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Никогда' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('users.show', $user) }}"
                                   class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition"
                                   title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('users.edit', $user) }}"
                                   class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded-lg transition"
                                   title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if($user->id !== auth()->id())
                                    <button onclick="changeRole({{ $user->id }}, '{{ $user->role }}')"
                                            class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition"
                                            title="Сменить роль">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>

                                    @if($user->submissions_count == 0)
                                        <form action="{{ route('users.destroy', $user) }}"
                                              method="POST"
                                              onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')"
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
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Пагинация -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Модальное окно для смены роли -->
    <div id="roleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Изменение роли пользователя</h3>
                <form id="roleForm" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Новая роль</label>
                        <select name="role" id="newRole" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="participant">Участник</option>
                            <option value="jury">Член жюри</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                            Изменение роли повлияет на доступ пользователя к функциям системы.
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRoleModal()"
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
        function changeRole(userId, currentRole) {
            const modal = document.getElementById('roleModal');
            const form = document.getElementById('roleForm');
            const select = document.getElementById('newRole');

            form.action = `/users/${userId}/change-role`;
            select.value = currentRole;

            modal.classList.remove('hidden');
        }

        function closeRoleModal() {
            document.getElementById('roleModal').classList.add('hidden');
        }

        // Закрытие по клику вне модального окна
        window.onclick = function(event) {
            const modal = document.getElementById('roleModal');
            if (event.target == modal) {
                closeRoleModal();
            }
        }
    </script>
@endsection
