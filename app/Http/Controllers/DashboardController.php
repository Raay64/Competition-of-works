<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Submission;
use App\Models\SubmissionComment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Статистика для админа
            $stats = [
                'total_users' => User::count(),
                'users_by_role' => [
                    'participant' => User::where('role', 'participant')->count(),
                    'jury' => User::where('role', 'jury')->count(),
                    'admin' => User::where('role', 'admin')->count(),
                ],
                'total_contests' => Contest::count(),
                'active_contests' => Contest::where('is_active', true)->count(),
                'total_submissions' => Submission::count(),
                'submissions_by_status' => [
                    'draft' => Submission::where('status', 'draft')->count(),
                    'submitted' => Submission::where('status', 'submitted')->count(),
                    'needs_fix' => Submission::where('status', 'needs_fix')->count(),
                    'accepted' => Submission::where('status', 'accepted')->count(),
                    'rejected' => Submission::where('status', 'rejected')->count(),
                ],
            ];

            $recent_submissions = Submission::with(['user', 'contest'])
                ->latest()
                ->take(10)
                ->get();

            $recent_users = User::latest()
                ->take(5)
                ->get();

            $contests = Contest::withCount('submissions')
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard.admin', compact('stats', 'recent_submissions', 'recent_users', 'contests'));

        } elseif ($user->isJury()) {
            // Статистика для жюри
            $stats = [
                'total_submissions' => Submission::count(),
                'pending_review' => Submission::where('status', 'submitted')->count(),
                'needs_fix' => Submission::where('status', 'needs_fix')->count(),
                'accepted' => Submission::where('status', 'accepted')->count(),
                'rejected' => Submission::where('status', 'rejected')->count(),
            ];

            $submissions_to_review = Submission::with(['user', 'contest'])
                ->whereIn('status', ['submitted', 'needs_fix'])
                ->latest()
                ->get();

            $recent_decisions = Submission::whereIn('status', ['accepted', 'rejected'])
                ->with(['user', 'contest'])
                ->latest()
                ->take(10)
                ->get();

            return view('dashboard.jury', compact('stats', 'submissions_to_review', 'recent_decisions'));

        } else {
            // Статистика для участника
            $stats = [
                'total_submissions' => $user->submissions()->count(),
                'draft' => $user->submissions()->where('status', 'draft')->count(),
                'submitted' => $user->submissions()->where('status', 'submitted')->count(),
                'needs_fix' => $user->submissions()->where('status', 'needs_fix')->count(),
                'accepted' => $user->submissions()->where('status', 'accepted')->count(),
                'rejected' => $user->submissions()->where('status', 'rejected')->count(),
            ];

            $submissions = $user->submissions()
                ->with('contest')
                ->latest()
                ->get();

            $active_contests = Contest::where('is_active', true)
                ->where('deadline_at', '>', now())
                ->get();

            $recent_comments = SubmissionComment::whereHas('submission', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->with(['submission', 'user'])
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard.participant', compact('stats', 'submissions', 'active_contests', 'recent_comments'));
        }
    }

    public function statistics()
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isJury()) {
            abort(403);
        }

        // Основная статистика как в админке
        $stats = [
            'total_users' => User::count(),
            'users_by_role' => [
                'participant' => User::where('role', 'participant')->count(),
                'jury' => User::where('role', 'jury')->count(),
                'admin' => User::where('role', 'admin')->count(),
            ],
            'total_contests' => Contest::count(),
            'active_contests' => Contest::where('is_active', true)->count(),
            'total_submissions' => Submission::count(),
            'submissions_by_status' => [
                'draft' => Submission::where('status', 'draft')->count(),
                'submitted' => Submission::where('status', 'submitted')->count(),
                'needs_fix' => Submission::where('status', 'needs_fix')->count(),
                'accepted' => Submission::where('status', 'accepted')->count(),
                'rejected' => Submission::where('status', 'rejected')->count(),
            ],
        ];

        // Для обратной совместимости со старыми переменными
        $users_total = $stats['total_users'];
        $contests_total = $stats['total_contests'];
        $submissions_total = $stats['total_submissions'];
        $submissions_by_status = $stats['submissions_by_status'];

        // Детальная статистика для графиков
        $submissions_by_day = Submission::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $submissions_by_contest = Contest::withCount('submissions')
            ->having('submissions_count', '>', 0)
            ->get();

        $avg_response_time = Submission::whereIn('status', ['accepted', 'rejected'])
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->first();

        $active_users = User::whereHas('submissions', function($q) {
            $q->where('created_at', '>=', now()->subDays(7));
        })->count();

        $attachments_total = Submission::count();

        return view('dashboard.statistics', compact(
            'stats',
            'users_total',
            'contests_total',
            'submissions_total',
            'attachments_total',
            'submissions_by_status',
            'submissions_by_day',
            'submissions_by_contest',
            'avg_response_time',
            'active_users'
        ));
    }
}
