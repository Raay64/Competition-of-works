<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Contest;
use App\Models\User;
use App\Services\SubmissionService;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    protected SubmissionService $submissionService;
    protected AttachmentService $attachmentService;

    public function __construct(SubmissionService $submissionService, AttachmentService $attachmentService)
    {
        $this->submissionService = $submissionService;
        $this->attachmentService = $attachmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isJury() || $user->isAdmin()) {
            // Для жюри и админа - все работы
            $query = Submission::with(['user', 'contest']);

            // Фильтр по статусу
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Фильтр по конкурсу
            if ($request->filled('contest_id')) {
                $query->where('contest_id', $request->contest_id);
            }

            // Фильтр по пользователю
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Поиск по названию
            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $submissions = $query->latest()->paginate(15)->withQueryString();

            // Данные для фильтров
            $contests = Contest::all();
            $users = User::where('role', 'participant')->get();
            $statuses = [
                'draft' => 'Черновик',
                'submitted' => 'На проверке',
                'needs_fix' => 'Требуется доработка',
                'accepted' => 'Принято',
                'rejected' => 'Отклонено'
            ];

            return view('submissions.index', compact('submissions', 'contests', 'users', 'statuses'));

        } else {
            // Для участника - только свои работы
            $submissions = $user->submissions()
                ->with('contest')
                ->latest()
                ->paginate(15);

            return view('submissions.my', compact('submissions'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $contests = Contest::where('is_active', true)
            ->where('deadline_at', '>', now())
            ->get();

        if ($contests->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Нет активных конкурсов для подачи работ');
        }

        return view('submissions.create', compact('contests'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contest_id' => 'required|exists:contests,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $submission = $this->submissionService->create($request->all(), Auth::user());

            return redirect()->route('submissions.show', $submission)
                ->with('success', 'Черновик работы успешно создан');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Submission $submission)
    {
        $user = Auth::user();

        // Проверка доступа
        if ($user->isParticipant() && $submission->user_id !== $user->id) {
            abort(403, 'У вас нет доступа к этой работе');
        }

        // Загружаем связи
        $submission->load(['user', 'contest', 'attachments', 'comments.user']);

        // Статистика файлов
        $attachments_stats = [
            'total' => $submission->attachments->count(),
            'scanned' => $submission->attachments->where('status', 'scanned')->count(),
            'pending' => $submission->attachments->where('status', 'pending')->count(),
            'rejected' => $submission->attachments->where('status', 'rejected')->count(),
        ];

        // Можно ли редактировать
        $can_edit = $submission->canBeEdited() && $submission->user_id === $user->id;

        // Можно ли отправлять
        $can_submit = $submission->status === 'draft' &&
            $attachments_stats['scanned'] > 0 &&
            $submission->user_id === $user->id;

        // Можно ли загружать файлы
        $can_upload = $submission->user_id === $user->id &&
            $submission->canBeEdited() &&
            $attachments_stats['total'] < 3;

        // Доступные статусы для жюри/админа
        $available_statuses = [];
        $statusNames = [
            'draft' => 'Черновик',
            'submitted' => 'На проверке',
            'needs_fix' => 'Требуется доработка',
            'accepted' => 'Принято',
            'rejected' => 'Отклонено'
        ];

        if ($user->isJury() || $user->isAdmin()) {
            $available_statuses = Submission::getAllowedStatusTransitions()[$submission->status] ?? [];
        }

        return view('submissions.show', compact(
            'submission',
            'attachments_stats',
            'can_edit',
            'can_submit',
            'can_upload',
            'available_statuses',
            'statusNames'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Submission $submission)
    {
        $user = Auth::user();

        if ($submission->user_id !== $user->id) {
            abort(403);
        }

        if (!$submission->canBeEdited()) {
            return redirect()->route('submissions.show', $submission)
                ->with('error', 'Редактирование недоступно в текущем статусе');
        }

        return view('submissions.edit', compact('submission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Submission $submission)
    {
        $user = Auth::user();

        if ($submission->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $this->submissionService->update($submission, $request->all());

            return redirect()->route('submissions.show', $submission)
                ->with('success', 'Работа успешно обновлена');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Submit submission for review
     */
    public function submit(Submission $submission)
    {
        $user = Auth::user();

        if ($submission->user_id !== $user->id) {
            abort(403);
        }

        try {
            $this->submissionService->submit($submission);

            return redirect()->route('submissions.show', $submission)
                ->with('success', 'Работа отправлена на проверку');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Change submission status (for jury)
     */
    public function changeStatus(Request $request, Submission $submission)
    {
        $user = Auth::user();

        if (!$user->isJury() && !$user->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'У вас нет прав для изменения статуса'
                ], 403);
            }
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:submitted,needs_fix,accepted,rejected',
            'comment' => 'nullable|string|max:5000',
        ]);

        // Проверяем, допустим ли такой переход
        if (!$submission->canJurySetStatus($request->status)) {
            return back()->with('error', 'Недопустимый переход статуса')
                ->withInput();
        }

        try {
            $this->submissionService->changeStatus($submission, $request->status);

            // Добавляем комментарий, если он есть
            if ($request->filled('comment')) {
                $this->submissionService->addComment(
                    $submission,
                    $user,
                    $request->comment
                );
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Статус работы изменен',
                    'new_status' => $request->status
                ]);
            }

            return redirect()->route('submissions.show', $submission)
                ->with('success', 'Статус работы изменен');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }

            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Export all submissions (for admin/jury)
     */
    public function exportAll(Request $request)
    {
        $user = Auth::user();

        if (!$user->isJury() && !$user->isAdmin()) {
            abort(403);
        }

        // Здесь будет логика экспорта
        return redirect()->back()->with('success', 'Экспорт в разработке');
    }
}
