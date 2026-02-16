<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Публичные маршруты
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Аутентификация
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Защищенные маршруты (требуется аутентификация)
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [DashboardController::class, 'statistics'])->name('statistics');

    // Конкурсы (Contests)
    Route::resource('contests', ContestController::class);
    Route::get('/contests/{contest}/export', [ContestController::class, 'export'])->name('contests.export');

    // Работы (Submissions)
    Route::resource('submissions', SubmissionController::class);
    Route::post('/submissions/{submission}/submit', [SubmissionController::class, 'submit'])->name('submissions.submit');
    Route::post('/submissions/{submission}/change-status', [SubmissionController::class, 'changeStatus'])->name('submissions.change-status');
    Route::get('/submissions/export/all', [SubmissionController::class, 'exportAll'])->name('submissions.export');

    // Комментарии (Comments)
    Route::post('/submissions/{submission}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/mark-helpful', [CommentController::class, 'markAsHelpful'])->name('comments.mark-helpful');
    Route::get('/submissions/{submission}/comments/history', [CommentController::class, 'history'])->name('comments.history');

    // Файлы (Attachments)
    Route::post('/submissions/{submission}/attachments', [AttachmentController::class, 'upload'])->name('attachments.upload');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('/attachments/{attachment}/preview', [AttachmentController::class, 'preview'])->name('attachments.preview');

    // Пользователи (Users)
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/change-role', [UserController::class, 'changeRole'])->name('users.change-role');
    Route::post('/users/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('users.toggle-block');
    Route::get('/users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');

    // Уведомления (Notifications)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [NotificationController::class, 'updateSettings'])->name('update-settings');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
        Route::get('/unread/count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    });
});

// API маршруты (для AJAX запросов)
Route::prefix('api')->middleware(['auth'])->group(function () {
    // API для комментариев
    Route::get('/submissions/{submission}/comments', [CommentController::class, 'history']);

    // API для уведомлений
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);

    // API для статистики
    Route::get('/statistics/submissions-by-day', [DashboardController::class, 'submissionsByDay']);
    Route::get('/statistics/submissions-by-status', [DashboardController::class, 'submissionsByStatus']);
});

// Тестовые маршруты (только для разработки)
if (app()->environment('local')) {
    Route::get('/test/email', function() {
        return view('emails.status-changed', ['data' => [
            'submission_title' => 'Тестовая работа',
            'old_status' => 'draft',
            'new_status' => 'submitted',
            'changed_at' => now()->format('d.m.Y H:i')
        ]]);
    });
}
