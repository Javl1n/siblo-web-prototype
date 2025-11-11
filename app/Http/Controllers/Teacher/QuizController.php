<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Quiz::where('created_by', auth()->id())
            ->with('questions')
            ->withCount('attempts');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        if ($request->filled('difficulty')) {
            $query->difficulty($request->difficulty);
        }

        if ($request->filled('subject')) {
            $query->subject($request->subject);
        }

        $quizzes = $query->latest()->paginate(15);

        $subjects = Quiz::where('created_by', auth()->id())
            ->distinct()
            ->pluck('subject');

        return Inertia::render('teacher/quizzes/index', [
            'quizzes' => $quizzes,
            'subjects' => $subjects,
            'filters' => $request->only(['search', 'status', 'difficulty', 'subject']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('teacher/quizzes/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'pass_threshold' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.question_type' => ['required', 'in:multiple_choice,true_false,fill_blank,multiple_correct'],
            'questions.*.points' => ['required', 'integer', 'min:1'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.choices' => ['required', 'array', 'min:2'],
            'questions.*.choices.*.choice_text' => ['required', 'string', 'max:500'],
            'questions.*.choices.*.is_correct' => ['required', 'boolean'],
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'topic' => $validated['topic'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'pass_threshold' => $validated['pass_threshold'],
            'max_attempts' => $validated['max_attempts'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
            'is_featured' => $validated['is_featured'] ?? false,
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        foreach ($validated['questions'] as $index => $questionData) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'points' => $questionData['points'],
                'order_number' => $index + 1,
                'explanation' => $questionData['explanation'] ?? null,
            ]);

            foreach ($questionData['choices'] as $choiceIndex => $choiceData) {
                QuestionChoice::create([
                    'question_id' => $question->id,
                    'choice_text' => $choiceData['choice_text'],
                    'is_correct' => $choiceData['is_correct'],
                    'order_number' => $choiceIndex + 1,
                ]);
            }
        }

        $quiz->calculateTotalPoints();

        return redirect()->route('teacher.quizzes.show', $quiz)
            ->with('success', 'Quiz created successfully!');
    }

    public function show(Quiz $quiz): Response
    {
        $this->authorize('view', $quiz);

        $quiz->load(['questions.choices', 'attempts.student', 'analytics']);

        $stats = [
            'total_attempts' => $quiz->attempts()->count(),
            'completed_attempts' => $quiz->attempts()->where('is_completed', true)->count(),
            'average_score' => $quiz->attempts()
                ->where('is_completed', true)
                ->avg('percentage'),
            'pass_rate' => $quiz->attempts()
                ->where('is_completed', true)
                ->where('percentage', '>=', $quiz->pass_threshold)
                ->count() / max($quiz->attempts()->where('is_completed', true)->count(), 1) * 100,
        ];

        return Inertia::render('teacher/quizzes/show', [
            'quiz' => $quiz,
            'stats' => $stats,
        ]);
    }

    public function edit(Quiz $quiz): Response
    {
        $this->authorize('update', $quiz);

        $quiz->load('questions.choices');

        return Inertia::render('teacher/quizzes/edit', [
            'quiz' => $quiz,
        ]);
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'pass_threshold' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.question_type' => ['required', 'in:multiple_choice,true_false,fill_blank,multiple_correct'],
            'questions.*.points' => ['required', 'integer', 'min:1'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.choices' => ['required', 'array', 'min:2'],
            'questions.*.choices.*.choice_text' => ['required', 'string', 'max:500'],
            'questions.*.choices.*.is_correct' => ['required', 'boolean'],
        ]);

        $quiz->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'topic' => $validated['topic'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'pass_threshold' => $validated['pass_threshold'],
            'max_attempts' => $validated['max_attempts'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
            'is_featured' => $validated['is_featured'] ?? false,
        ]);

        $quiz->questions()->each(function ($question) {
            $question->choices()->delete();
            $question->delete();
        });

        foreach ($validated['questions'] as $index => $questionData) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'points' => $questionData['points'],
                'order_number' => $index + 1,
                'explanation' => $questionData['explanation'] ?? null,
            ]);

            foreach ($questionData['choices'] as $choiceIndex => $choiceData) {
                QuestionChoice::create([
                    'question_id' => $question->id,
                    'choice_text' => $choiceData['choice_text'],
                    'is_correct' => $choiceData['is_correct'],
                    'order_number' => $choiceIndex + 1,
                ]);
            }
        }

        $quiz->calculateTotalPoints();

        return redirect()->route('teacher.quizzes.show', $quiz)
            ->with('success', 'Quiz updated successfully!');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return redirect()->route('teacher.quizzes.index')
            ->with('success', 'Quiz deleted successfully!');
    }

    public function togglePublish(Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        $quiz->update([
            'is_published' => ! $quiz->is_published,
        ]);

        $message = $quiz->is_published
            ? 'Quiz published successfully!'
            : 'Quiz unpublished successfully!';

        return back()->with('success', $message);
    }
}
