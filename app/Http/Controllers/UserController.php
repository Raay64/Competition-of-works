<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\ChangeRoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Список пользователей (только админ)
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Фильтр по роли
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Поиск
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (in_array($sort, ['name', 'email', 'role', 'created_at'])) {
            $query->orderBy($sort, $direction);
        }

        $users = $query->withCount('submissions')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'participants' => User::where('role', 'participant')->count(),
            'jury' => User::where('role', 'jury')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Форма создания пользователя (только админ)
     */
    public function create()
    {
        return view('users.form');
    }

    /**
     * Сохранение нового пользователя (только админ)
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return redirect()->route('users.show', $user)
            ->with('success', 'Пользователь успешно создан');
    }

    /**
     * Просмотр профиля пользователя
     */
    public function show(User $user)
    {
        // Пользователь может видеть свой профиль, админ - любой
        if (Auth::id() !== $user->id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $submissions = $user->submissions()
            ->with('contest')
            ->withCount('attachments')
            ->latest()
            ->paginate(10);

        $stats = [
            'total_submissions' => $user->submissions()->count(),
            'draft' => $user->submissions()->where('status', 'draft')->count(),
            'submitted' => $user->submissions()->where('status', 'submitted')->count(),
            'needs_fix' => $user->submissions()->where('status', 'needs_fix')->count(),
            'accepted' => $user->submissions()->where('status', 'accepted')->count(),
            'rejected' => $user->submissions()->where('status', 'rejected')->count(),
        ];

        return view('users.show', compact('user', 'submissions', 'stats'));
    }

    /**
     * Форма редактирования пользователя
     */
    public function edit(User $user)
    {
        // Пользователь может редактировать свой профиль, админ - любой
        if (Auth::id() !== $user->id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('users.form', compact('user'));
    }

    /**
     * Обновление пользователя
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.show', $user)
            ->with('success', 'Профиль успешно обновлен');
    }

    /**
     * Удаление пользователя (только админ)
     */
    public function destroy(User $user)
    {
        if ($user->submissions()->exists()) {
            return back()->with('error', 'Нельзя удалить пользователя, у которого есть работы');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Нельзя удалить самого себя');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Пользователь удален');
    }

    /**
     * Смена роли пользователя (быстрое действие для админа)
     */
    public function changeRole(ChangeRoleRequest $request, User $user)
    {
        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Роль пользователя изменена',
        ]);
    }

    /**
     * Блокировка/разблокировка пользователя (если добавить поле blocked_at)
     */
    public function toggleBlock(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Нельзя заблокировать самого себя');
        }


        return back()->with('success', 'Статус пользователя изменен');
    }
}
