import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Quiz {
    id: number;
    title: string;
    description: string | null;
    subject: string;
    topic: string | null;
    difficulty_level: 'easy' | 'medium' | 'hard';
    is_published: boolean;
    is_featured: boolean;
    questions_count: number;
    attempts_count: number;
    created_at: string;
}

interface PaginatedQuizzes {
    data: Quiz[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface IndexProps {
    quizzes: PaginatedQuizzes;
    subjects: string[];
    filters: {
        search?: string;
        status?: string;
        difficulty?: string;
        subject?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/teacher/dashboard',
    },
    {
        title: 'Quizzes',
        href: '/teacher/quizzes',
    },
];

const difficultyColors = {
    easy: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    medium:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    hard: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
};

export default function Index({ quizzes, subjects, filters }: IndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');
    const [difficulty, setDifficulty] = useState(filters.difficulty || 'all');
    const [subject, setSubject] = useState(filters.subject || 'all');

    const handleFilter = () => {
        router.get(
            '/teacher/quizzes',
            {
                search: search || undefined,
                status: status !== 'all' ? status : undefined,
                difficulty: difficulty !== 'all' ? difficulty : undefined,
                subject: subject !== 'all' ? subject : undefined,
            },
            { preserveState: true },
        );
    };

    const handleClearFilters = () => {
        setSearch('');
        setStatus('all');
        setDifficulty('all');
        setSubject('all');
        router.get('/teacher/quizzes');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Quizzes" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Quizzes
                        </h1>
                        <p className="text-muted-foreground">
                            Manage your quizzes and track student performance
                        </p>
                    </div>
                    <Link href="/teacher/quizzes/create">
                        <Button>Create New Quiz</Button>
                    </Link>
                </div>

                <Card className="p-6">
                    <div className="mb-4 grid gap-4 md:grid-cols-4">
                        <Input
                            placeholder="Search quizzes..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    handleFilter();
                                }
                            }}
                        />
                        <Select value={status} onValueChange={setStatus}>
                            <SelectTrigger>
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="published">
                                    Published
                                </SelectItem>
                                <SelectItem value="draft">Draft</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select
                            value={difficulty}
                            onValueChange={setDifficulty}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Difficulty" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All Difficulties
                                </SelectItem>
                                <SelectItem value="easy">Easy</SelectItem>
                                <SelectItem value="medium">Medium</SelectItem>
                                <SelectItem value="hard">Hard</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select value={subject} onValueChange={setSubject}>
                            <SelectTrigger>
                                <SelectValue placeholder="Subject" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All Subjects
                                </SelectItem>
                                {subjects.map((subj) => (
                                    <SelectItem key={subj} value={subj}>
                                        {subj}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="flex gap-2">
                        <Button onClick={handleFilter}>Apply Filters</Button>
                        <Button variant="outline" onClick={handleClearFilters}>
                            Clear Filters
                        </Button>
                    </div>
                </Card>

                {quizzes.data.length === 0 ? (
                    <Card className="flex flex-col items-center justify-center p-12 text-center">
                        <p className="mb-4 text-muted-foreground">
                            No quizzes found. Create your first quiz to get
                            started!
                        </p>
                        <Link href="/teacher/quizzes/create">
                            <Button>Create New Quiz</Button>
                        </Link>
                    </Card>
                ) : (
                    <div className="flex flex-col gap-4">
                        {quizzes.data.map((quiz) => (
                            <Card key={quiz.id} className="p-6">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <div className="mb-2 flex items-center gap-2">
                                            <Link
                                                href={`/teacher/quizzes/${quiz.id}`}
                                                className="text-xl font-semibold hover:underline"
                                            >
                                                {quiz.title}
                                            </Link>
                                            {quiz.is_featured && (
                                                <Badge variant="default">
                                                    Featured
                                                </Badge>
                                            )}
                                            <Badge
                                                variant={
                                                    quiz.is_published
                                                        ? 'default'
                                                        : 'secondary'
                                                }
                                            >
                                                {quiz.is_published
                                                    ? 'Published'
                                                    : 'Draft'}
                                            </Badge>
                                            <Badge
                                                className={
                                                    difficultyColors[
                                                        quiz.difficulty_level
                                                    ]
                                                }
                                            >
                                                {quiz.difficulty_level}
                                            </Badge>
                                        </div>
                                        {quiz.description && (
                                            <p className="mb-3 text-sm text-muted-foreground">
                                                {quiz.description}
                                            </p>
                                        )}
                                        <div className="flex flex-wrap gap-4 text-sm text-muted-foreground">
                                            <span>
                                                Subject: {quiz.subject}
                                            </span>
                                            {quiz.topic && (
                                                <span>Topic: {quiz.topic}</span>
                                            )}
                                            <span>
                                                {quiz.questions_count}{' '}
                                                questions
                                            </span>
                                            <span>
                                                {quiz.attempts_count} attempts
                                            </span>
                                            <span>
                                                Created:{' '}
                                                {new Date(
                                                    quiz.created_at,
                                                ).toLocaleDateString()}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex gap-2">
                                        <Link
                                            href={`/teacher/quizzes/${quiz.id}/edit`}
                                        >
                                            <Button
                                                variant="outline"
                                                size="sm"
                                            >
                                                Edit
                                            </Button>
                                        </Link>
                                        <Link
                                            href={`/teacher/quizzes/${quiz.id}`}
                                        >
                                            <Button size="sm">
                                                View Details
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            </Card>
                        ))}

                        {quizzes.last_page > 1 && (
                            <div className="flex items-center justify-center gap-2">
                                {Array.from(
                                    { length: quizzes.last_page },
                                    (_, i) => i + 1,
                                ).map((page) => (
                                    <Button
                                        key={page}
                                        variant={
                                            page === quizzes.current_page
                                                ? 'default'
                                                : 'outline'
                                        }
                                        size="sm"
                                        onClick={() =>
                                            router.get(
                                                `/teacher/quizzes?page=${page}`,
                                            )
                                        }
                                    >
                                        {page}
                                    </Button>
                                ))}
                            </div>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
