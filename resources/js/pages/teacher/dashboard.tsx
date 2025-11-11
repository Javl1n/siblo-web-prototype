import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface QuizAttempt {
    id: number;
    student: {
        id: number;
        name: string;
        username: string;
    };
    quiz: {
        id: number;
        title: string;
    };
    score: number;
    max_score: number;
    percentage: number;
    submitted_at: string;
}

interface Stats {
    total_quizzes: number;
    published_quizzes: number;
    total_students: number;
    recent_attempts: QuizAttempt[];
}

interface DashboardProps {
    stats: Stats;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/teacher/dashboard',
    },
];

export default function Dashboard({ stats }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Teacher Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Dashboard
                        </h1>
                        <p className="text-muted-foreground">
                            Welcome back! Here's an overview of your teaching
                            activity.
                        </p>
                    </div>
                    <Link href="/teacher/quizzes/create">
                        <Button>Create New Quiz</Button>
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="p-6">
                        <div className="flex flex-col gap-2">
                            <p className="text-sm font-medium text-muted-foreground">
                                Total Quizzes
                            </p>
                            <p className="text-3xl font-bold">
                                {stats.total_quizzes}
                            </p>
                            <p className="text-xs text-muted-foreground">
                                {stats.published_quizzes} published
                            </p>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <div className="flex flex-col gap-2">
                            <p className="text-sm font-medium text-muted-foreground">
                                Published Quizzes
                            </p>
                            <p className="text-3xl font-bold">
                                {stats.published_quizzes}
                            </p>
                            <p className="text-xs text-muted-foreground">
                                Available to students
                            </p>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <div className="flex flex-col gap-2">
                            <p className="text-sm font-medium text-muted-foreground">
                                Total Students
                            </p>
                            <p className="text-3xl font-bold">
                                {stats.total_students}
                            </p>
                            <p className="text-xs text-muted-foreground">
                                On the platform
                            </p>
                        </div>
                    </Card>
                </div>

                <div className="flex flex-col gap-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-xl font-semibold">
                            Recent Quiz Attempts
                        </h2>
                        <Link href="/teacher/students">
                            <Button variant="outline" size="sm">
                                View All Students
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        {stats.recent_attempts.length === 0 ? (
                            <div className="flex flex-col items-center justify-center p-12 text-center">
                                <p className="text-muted-foreground">
                                    No quiz attempts yet. Create and publish a
                                    quiz to get started!
                                </p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {stats.recent_attempts.map((attempt) => (
                                    <div
                                        key={attempt.id}
                                        className="flex items-center justify-between p-4 hover:bg-muted/50"
                                    >
                                        <div className="flex flex-col gap-1">
                                            <Link
                                                href={`/teacher/students/${attempt.student.id}`}
                                                className="font-medium hover:underline"
                                            >
                                                {attempt.student.name}
                                            </Link>
                                            <Link
                                                href={`/teacher/quizzes/${attempt.quiz.id}`}
                                                className="text-sm text-muted-foreground hover:underline"
                                            >
                                                {attempt.quiz.title}
                                            </Link>
                                        </div>
                                        <div className="flex items-center gap-4">
                                            <div className="text-right">
                                                <p className="text-sm font-medium">
                                                    {Math.round(
                                                        attempt.percentage,
                                                    )}
                                                    %
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {attempt.score}/
                                                    {attempt.max_score} points
                                                </p>
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {new Date(
                                                    attempt.submitted_at,
                                                ).toLocaleDateString()}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card className="p-6">
                        <h3 className="mb-4 text-lg font-semibold">
                            Quick Actions
                        </h3>
                        <div className="flex flex-col gap-2">
                            <Link href="/teacher/quizzes/create">
                                <Button variant="outline" className="w-full">
                                    Create New Quiz
                                </Button>
                            </Link>
                            <Link href="/teacher/quizzes">
                                <Button variant="outline" className="w-full">
                                    View All Quizzes
                                </Button>
                            </Link>
                            <Link href="/teacher/students">
                                <Button variant="outline" className="w-full">
                                    View All Students
                                </Button>
                            </Link>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <h3 className="mb-4 text-lg font-semibold">
                            Getting Started
                        </h3>
                        <div className="flex flex-col gap-3 text-sm text-muted-foreground">
                            <p>
                                1. Create your first quiz with multiple-choice
                                questions
                            </p>
                            <p>2. Publish the quiz to make it available</p>
                            <p>
                                3. Students can access quizzes through the game
                                client
                            </p>
                            <p>
                                4. Track student performance and view analytics
                            </p>
                        </div>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
