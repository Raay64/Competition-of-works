<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Contest::query();

        // Фильтры
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->has('deadline')) {
            if ($request->deadline === 'upcoming') {
                $query->where('deadline_at', '>', now());
            } elseif ($request->deadline === 'passed') {
                $query->where('deadline_at', '<', now());
            }
        }

        // Поиск
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Для участников показываем только активные конкурсы с дейдлайном в будущем
        if ($user->isParticipant()) {
            $contests = Contest::where('is_active', true)
                ->where('deadline_at', '>', now())
                ->withCount('submissions')
                ->latest()
                ->paginate(15);
        } else {
            $contests = $query->withCount('submissions')
                ->latest()
                ->paginate(15);
        }

        return view('contests.index', compact('contests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contests.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline_at' => 'required|date|after:today',
            'is_active' => 'boolean',
        ]);

        $contest = Contest::create($data);

        return redirect()->route('contests.show', $contest)
            ->with('success', 'Конкурс успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contest $contest)
    {
        $user = Auth::user();

        // Загружаем работы для этого конкурса с пагинацией
        if ($user->isAdmin() || $user->isJury()) {
            $submissions = $contest->submissions()
                ->with('user')
                ->withCount('attachments')
                ->latest()
                ->paginate(20); // Используем paginate() вместо get()
        } else {
            // Участник видит только свои работы
            $submissions = $contest->submissions()
                ->where('user_id', $user->id)
                ->withCount('attachments')
                ->latest()
                ->paginate(20); // Используем paginate() вместо get()
        }

        // Статистика по конкурсу
        $stats = [
            'total_submissions' => $contest->submissions()->count(),
            'submissions_by_status' => [
                'draft' => $contest->submissions()->where('status', 'draft')->count(),
                'submitted' => $contest->submissions()->where('status', 'submitted')->count(),
                'needs_fix' => $contest->submissions()->where('status', 'needs_fix')->count(),
                'accepted' => $contest->submissions()->where('status', 'accepted')->count(),
                'rejected' => $contest->submissions()->where('status', 'rejected')->count(),
            ],
        ];

        return view('contests.show', compact('contest', 'submissions', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contest $contest)
    {
        return view('contests.form', compact('contest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contest $contest)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline_at' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $contest->update($data);

        return redirect()->route('contests.show', $contest)
            ->with('success', 'Конкурс успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contest $contest)
    {
        if ($contest->submissions()->exists()) {
            return back()->with('error', 'Нельзя удалить конкурс, в котором есть работы');
        }

        $contest->delete();

        return redirect()->route('contests.index')
            ->with('success', 'Конкурс удален');
    }

    /**
     * Export submissions for a contest (admin and jury)
     */
    public function export(Contest $contest)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isJury()) {
            abort(403);
        }

        $submissions = $contest->submissions()
            ->with('user')
            ->withCount('attachments')
            ->get();

        $filename = 'contest_' . $contest->id . '_submissions_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($submissions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Участник', 'Email', 'Название работы', 'Статус', 'Файлов', 'Дата подачи']);

            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->id,
                    $submission->user->name,
                    $submission->user->email,
                    $submission->title,
                    $submission->status,
                    $submission->attachments_count,
                    $submission->created_at->format('d.m.Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
