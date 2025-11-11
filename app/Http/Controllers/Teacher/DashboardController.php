<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $teacher = auth()->user();

        $stats = [
            'total_quizzes' => Quiz::where('created_by', $teacher->id)->count(),
            'published_quizzes' => Quiz::where('created_by', $teacher->id)
                ->where('is_published', true)
                ->count(),
            'total_students' => User::where('user_type', 'student')->count(),
            'recent_attempts' => QuizAttempt::whereHas('quiz', function ($query) use ($teacher) {
                $query->where('created_by', $teacher->id);
            })
                ->with(['student', 'quiz'])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return Inertia::render('teacher/dashboard', [
            'stats' => $stats,
        ]);
    }
}
