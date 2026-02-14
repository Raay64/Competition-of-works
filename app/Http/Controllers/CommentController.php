<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionComment;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected SubmissionService $submissionService;

    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Сохранить новый комментарий
     */
    public function store(Request $request, Submission $submission)
    {
        $user = Auth::user();

        // Проверка доступа
        if ($user->isParticipant() && $submission->user_id !== $user->id) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Вы не можете комментировать эту работу'
                ], 403);
            }
            abort(403, 'Вы не можете комментировать эту работу');
        }

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        try {
            $comment = $this->submissionService->addComment(
                $submission,
                $user,
                $data['body']
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'comment' => $comment->load('user'),
                    'message' => 'Комментарий добавлен'
                ]);
            }

            return redirect()->back()->with('success', 'Комментарий добавлен')
                ->withFragment('comments');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }

            return redirect()->back()
                ->with('error', 'Ошибка при добавлении комментария: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Обновить комментарий
     */
    public function update(Request $request, SubmissionComment $comment)
    {
        $user = Auth::user();

        // Только автор комментария или админ могут редактировать
        if ($user->id !== $comment->user_id && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'У вас нет прав на редактирование этого комментария'
                ], 403);
            }
            abort(403);
        }

        // Нельзя редактировать комментарии старше 1 часа (опционально)
        if ($comment->created_at->diffInHours(now()) > 1 && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Нельзя редактировать комментарии старше 1 часа'
                ], 403);
            }
            return redirect()->back()
                ->with('error', 'Нельзя редактировать комментарии старше 1 часа');
        }

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $comment->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->fresh('user'),
                'message' => 'Комментарий обновлен'
            ]);
        }

        return redirect()->back()->with('success', 'Комментарий обновлен');
    }

    /**
     * Удалить комментарий
     */
    public function destroy(Request $request, SubmissionComment $comment)
    {
        $user = Auth::user();

        // Только автор комментария или админ могут удалять
        if ($user->id !== $comment->user_id && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'У вас нет прав на удаление этого комментария'
                ], 403);
            }
            abort(403);
        }

        $comment->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Комментарий удален'
            ]);
        }

        return redirect()->back()->with('success', 'Комментарий удален');
    }

    /**
     * Пометить комментарий как полезный (для жюри)
     */
    public function markAsHelpful(Request $request, SubmissionComment $comment)
    {
        $user = Auth::user();

        // Только жюри и админ могут помечать комментарии
        if (!$user->isJury() && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Только жюри может помечать комментарии'
                ], 403);
            }
            abort(403);
        }

        // Переключаем статус is_helpful
        $comment->update([
            'is_helpful' => !$comment->is_helpful
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_helpful' => $comment->is_helpful,
                'message' => $comment->is_helpful ? 'Комментарий отмечен как полезный' : 'Отметка снята'
            ]);
        }

        return redirect()->back()->with('success',
            $comment->is_helpful ? 'Комментарий отмечен как полезный' : 'Отметка снята'
        );
    }

    /**
     * Получить историю комментариев к работе (для AJAX)
     */
    public function history(Request $request, Submission $submission)
    {
        $user = Auth::user();

        // Проверка доступа
        if ($user->isParticipant() && $submission->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Доступ запрещен'
            ], 403);
        }

        $comments = $submission->comments()
            ->with('user')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'role' => $comment->user->role,
                        'initials' => $comment->user->initials,
                    ],
                    'is_helpful' => $comment->is_helpful,
                    'can_edit' => Auth::id() === $comment->user_id || Auth::user()->isAdmin(),
                    'can_delete' => Auth::id() === $comment->user_id || Auth::user()->isAdmin(),
                    'created_at' => $comment->created_at->diffForHumans(),
                    'created_at_full' => $comment->created_at->format('d.m.Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }
}
