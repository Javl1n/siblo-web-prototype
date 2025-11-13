import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

interface Choice {
    id: number;
    choice_text: string;
    is_correct: boolean;
    order_number: number;
}

interface Question {
    id: number;
    question_text: string;
    question_type: string;
    points: number;
    order_number: number;
    explanation: string | null;
    choices: Choice[];
}

interface Student {
    id: number;
    name: string;
    email: string;
}

interface Attempt {
    id: number;
    student: Student;
    score: number;
    percentage: number;
    is_completed: boolean;
    completed_at: string | null;
}

interface Quiz {
    id: number;
    title: string;
    description: string | null;
    subject: string;
    topic: string | null;
    difficulty_level: string;
    time_limit_minutes: number | null;
    pass_threshold: number;
    max_attempts: number | null;
    total_points: number;
    is_published: boolean;
    is_featured: boolean;
    is_active: boolean;
    created_at: string;
    questions: Question[];
    attempts: Attempt[];
}

interface Stats {
    total_attempts: number;
    completed_attempts: number;
    average_score: number;
    pass_rate: number;
}

interface Props {
    quiz: Quiz;
    stats: Stats;
}

export default function Show({ quiz, stats }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/teacher/dashboard',
        },
        {
            title: 'Quizzes',
            href: '/teacher/quizzes',
        },
        {
            title: quiz.title,
            href: `/teacher/quizzes/${quiz.id}`,
        },
    ];

    const handleDelete = () => {
        if (
            confirm(
                'Are you sure you want to delete this quiz? This action cannot be undone.',
            )
        ) {
            router.delete(`/teacher/quizzes/${quiz.id}`);
        }
    };

    const handleTogglePublish = () => {
        router.post(`/teacher/quizzes/${quiz.id}/toggle-publish`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={quiz.title} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {quiz.title}
                        </h1>
                        {quiz.description && (
                            <p className="mt-2 text-muted-foreground">
                                {quiz.description}
                            </p>
                        )}
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant={quiz.is_published ? 'outline' : 'default'}
                            onClick={handleTogglePublish}
                        >
                            {quiz.is_published ? 'Unpublish' : 'Publish'}
                        </Button>
                        <Link href={`/teacher/quizzes/${quiz.id}/edit`}>
                            <Button variant="outline">Edit</Button>
                        </Link>
                        <Button variant="destructive" onClick={handleDelete}>
                            Delete
                        </Button>
                    </div>
                </div>

                {/* Quiz Details */}
                <div className="grid gap-6 md:grid-cols-3">
                    <Card className="p-4">
                        <h3 className="text-sm font-medium text-muted-foreground">
                            Subject
                        </h3>
                        <p className="mt-1 text-2xl font-semibold">
                            {quiz.subject}
                        </p>
                        {quiz.topic && (
                            <p className="text-sm text-muted-foreground">
                                {quiz.topic}
                            </p>
                        )}
                    </Card>

                    <Card className="p-4">
                        <h3 className="text-sm font-medium text-muted-foreground">
                            Difficulty
                        </h3>
                        <p className="mt-1 text-2xl font-semibold capitalize">
                            {quiz.difficulty_level}
                        </p>
                        <p className="text-sm text-muted-foreground">
                            {quiz.questions.length} questions •{' '}
                            {quiz.total_points} points
                        </p>
                    </Card>

                    <Card className="p-4">
                        <h3 className="text-sm font-medium text-muted-foreground">
                            Status
                        </h3>
                        <p className="mt-1 text-2xl font-semibold">
                            {quiz.is_published ? 'Published' : 'Draft'}
                        </p>
                        {quiz.time_limit_minutes && (
                            <p className="text-sm text-muted-foreground">
                                {quiz.time_limit_minutes} min time limit
                            </p>
                        )}
                    </Card>
                </div>

                {/* Statistics */}
                <Card className="p-6">
                    <h2 className="mb-4 text-xl font-semibold">Statistics</h2>
                    <div className="grid gap-4 md:grid-cols-4">
                        <div>
                            <h3 className="text-sm font-medium text-muted-foreground">
                                Total Attempts
                            </h3>
                            <p className="mt-1 text-2xl font-semibold">
                                {stats.total_attempts}
                            </p>
                        </div>
                        <div>
                            <h3 className="text-sm font-medium text-muted-foreground">
                                Completed
                            </h3>
                            <p className="mt-1 text-2xl font-semibold">
                                {stats.completed_attempts}
                            </p>
                        </div>
                        <div>
                            <h3 className="text-sm font-medium text-muted-foreground">
                                Average Score
                            </h3>
                            <p className="mt-1 text-2xl font-semibold">
                                {stats.average_score
                                    ? `${stats.average_score.toFixed(1)}%`
                                    : 'N/A'}
                            </p>
                        </div>
                        <div>
                            <h3 className="text-sm font-medium text-muted-foreground">
                                Pass Rate
                            </h3>
                            <p className="mt-1 text-2xl font-semibold">
                                {stats.pass_rate
                                    ? `${stats.pass_rate.toFixed(1)}%`
                                    : 'N/A'}
                            </p>
                        </div>
                    </div>
                </Card>

                {/* Questions */}
                <Card className="p-6">
                    <h2 className="mb-4 text-xl font-semibold">Questions</h2>
                    <div className="space-y-6">
                        {quiz.questions.map((question, index) => (
                            <Card key={question.id} className="p-4">
                                <div className="mb-3">
                                    <span className="text-sm font-medium text-muted-foreground">
                                        Question {index + 1} •{' '}
                                        {question.question_type.replace(
                                            '_',
                                            ' ',
                                        )}{' '}
                                        • {question.points} points
                                    </span>
                                    <p className="mt-1 text-lg font-medium">
                                        {question.question_text}
                                    </p>
                                </div>

                                <div className="space-y-2 pl-4">
                                    {question.choices.map((choice) => (
                                        <div
                                            key={choice.id}
                                            className={
                                                choice.is_correct
                                                    ? 'font-medium text-green-600 dark:text-green-400'
                                                    : 'text-muted-foreground'
                                            }
                                        >
                                            {choice.is_correct ? '✓' : '○'}{' '}
                                            {choice.choice_text}
                                        </div>
                                    ))}
                                </div>

                                {question.explanation && (
                                    <div className="mt-3 border-t pt-3">
                                        <span className="text-sm font-medium">
                                            Explanation:
                                        </span>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {question.explanation}
                                        </p>
                                    </div>
                                )}
                            </Card>
                        ))}
                    </div>
                </Card>

                {/* Recent Attempts */}
                {quiz.attempts.length > 0 && (
                    <Card className="p-6">
                        <h2 className="mb-4 text-xl font-semibold">
                            Recent Attempts
                        </h2>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b">
                                    <tr className="text-left">
                                        <th className="pb-3 font-medium">
                                            Student
                                        </th>
                                        <th className="pb-3 font-medium">
                                            Score
                                        </th>
                                        <th className="pb-3 font-medium">
                                            Percentage
                                        </th>
                                        <th className="pb-3 font-medium">
                                            Status
                                        </th>
                                        <th className="pb-3 font-medium">
                                            Completed At
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {quiz.attempts
                                        .slice(0, 10)
                                        .map((attempt) => (
                                            <tr key={attempt.id}>
                                                <td className="py-3">
                                                    {attempt.student.name}
                                                </td>
                                                <td className="py-3">
                                                    {attempt.score} /{' '}
                                                    {quiz.total_points}
                                                </td>
                                                <td className="py-3">
                                                    {attempt.percentage.toFixed(
                                                        1,
                                                    )}
                                                    %
                                                </td>
                                                <td className="py-3">
                                                    <span
                                                        className={
                                                            attempt.percentage >=
                                                            quiz.pass_threshold
                                                                ? 'text-green-600 dark:text-green-400'
                                                                : 'text-red-600 dark:text-red-400'
                                                        }
                                                    >
                                                        {attempt.percentage >=
                                                        quiz.pass_threshold
                                                            ? 'Passed'
                                                            : 'Failed'}
                                                    </span>
                                                </td>
                                                <td className="py-3">
                                                    {attempt.completed_at
                                                        ? new Date(
                                                              attempt.completed_at,
                                                          ).toLocaleDateString()
                                                        : 'In Progress'}
                                                </td>
                                            </tr>
                                        ))}
                                </tbody>
                            </table>
                        </div>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
