<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::where('user_type', 'student')
            ->with('playerProfile');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('username', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $students = $query->latest()->paginate(20);

        $students->getCollection()->transform(function ($student) {
            $completedQuizzes = $student->quizAttempts()
                ->where('is_completed', true)
                ->count();

            $averageScore = $student->quizAttempts()
                ->where('is_completed', true)
                ->avg('percentage');

            return [
                'id' => $student->id,
                'name' => $student->name,
                'username' => $student->username,
                'email' => $student->email,
                'trainer_name' => $student->playerProfile?->trainer_name,
                'level' => $student->playerProfile?->level ?? 1,
                'quizzes_completed' => $completedQuizzes,
                'average_score' => $averageScore ? round($averageScore, 1) : 0,
                'created_at' => $student->created_at,
            ];
        });

        return Inertia::render('teacher/students/index', [
            'students' => $students,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(User $student): Response
    {
        if ($student->user_type !== 'student') {
            abort(404);
        }

        $student->load([
            'playerProfile',
            'quizAttempts.quiz',
            'playerSiblons.species',
        ]);

        $quizAttempts = $student->quizAttempts()
            ->with('quiz')
            ->where('is_completed', true)
            ->latest()
            ->paginate(10);

        $subjectPerformance = $student->quizAttempts()
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quiz_attempts.is_completed', true)
            ->selectRaw('quizzes.subject, AVG(quiz_attempts.percentage) as avg_score, COUNT(*) as attempts_count')
            ->groupBy('quizzes.subject')
            ->get();

        $stats = [
            'total_quizzes_completed' => $student->quizAttempts()
                ->where('is_completed', true)
                ->count(),
            'average_score' => $student->quizAttempts()
                ->where('is_completed', true)
                ->avg('percentage'),
            'total_experience' => $student->playerProfile?->experience_points ?? 0,
            'total_coins' => $student->playerProfile?->coins ?? 0,
            'siblons_owned' => $student->playerSiblons()->count(),
        ];

        return Inertia::render('teacher/students/show', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'username' => $student->username,
                'email' => $student->email,
                'trainer_name' => $student->playerProfile?->trainer_name,
                'level' => $student->playerProfile?->level ?? 1,
                'created_at' => $student->created_at,
            ],
            'stats' => $stats,
            'quiz_attempts' => $quizAttempts,
            'subject_performance' => $subjectPerformance,
            'siblons' => $student->playerSiblons()->with('species')->get(),
        ]);
    }
}
