<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Requests\Notification\UpdateSettingsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Список уведомлений пользователя
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Notification::where('user_id', $user->id);

        // Фильтр по прочитанным/непрочитанным
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->filter === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        // Поиск по содержимому
        if ($request->filled('search')) {
            $query->where('data', 'like', '%' . $request->search . '%');
        }

        $notifications = $query->latest()->paginate(20);

        // Статистика
        $stats = [
            'total' => Notification::where('user_id', $user->id)->count(),
            'unread' => Notification::where('user_id', $user->id)->whereNull('read_at')->count(),
            'read' => Notification::where('user_id', $user->id)->whereNotNull('read_at')->count(),
        ];

        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Просмотр конкретного уведомления
     */
    public function show(Notification $notification)
    {
        // Проверка, что уведомление принадлежит пользователю
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        // Отмечаем как прочитанное
        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        // Декодируем данные для удобства
        $data = $notification->data;

        return view('notifications.show', compact('notification', 'data'));
    }

    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Уведомление отмечено как прочитанное'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Уведомление отмечено как прочитанное');
    }

    /**
     * Отметить все уведомления как прочитанные
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Все уведомления отмечены как прочитанные'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Все уведомления отмечены как прочитанные');
    }

    /**
     * Удалить уведомление
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Уведомление удалено'
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Уведомление удалено');
    }

    /**
     * Очистить все уведомления
     */
    public function clearAll()
    {
        Auth::user()->notifications()->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Все уведомления удалены'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Все уведомления удалены');
    }

    /**
     * Получить непрочитанные уведомления (для AJAX)
     */
    public function unread()
    {
        $notifications = Auth::user()
            ->notifications()
            ->whereNull('read_at')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'url' => route('notifications.show', $notification)
                ];
            });

        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'count' => $count,
            'notifications' => $notifications
        ]);
    }

    /**
     * Получить количество непрочитанных уведомлений
     */
    public function unreadCount()
    {
        return response()->json([
            'success' => true,
            'count' => Auth::user()->unreadNotifications()->count()
        ]);
    }

    /**
     * Настройки уведомлений
     */
    public function settings()
    {
        $user = Auth::user();

        // Получаем настройки из модели User
        $settings = $user->getNotificationSettings();

        return view('notifications.settings', compact('settings'));
    }

    /**
     * Сохранить настройки уведомлений
     */
    public function updateSettings(UpdateSettingsRequest $request)
    {
        $user = Auth::user();
        $user->updateNotificationSettings($request->validated());

        return redirect()->route('notifications.settings')
            ->with('success', 'Настройки уведомлений сохранены');
    }
}
