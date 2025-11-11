import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Student {
    id: number;
    name: string;
    username: string;
    email: string;
    trainer_name: string | null;
    level: number;
    quizzes_completed: number;
    average_score: number;
    created_at: string;
}

interface PaginatedStudents {
    data: Student[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface IndexProps {
    students: PaginatedStudents;
    filters: {
        search?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/teacher/dashboard',
    },
    {
        title: 'Students',
        href: '/teacher/students',
    },
];

export default function Index({ students, filters }: IndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = () => {
        router.get(
            '/teacher/students',
            { search: search || undefined },
            { preserveState: true },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Students" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Students
                    </h1>
                    <p className="text-muted-foreground">
                        View all students and their progress on the platform
                    </p>
                </div>

                <Card className="p-6">
                    <div className="flex gap-2">
                        <Input
                            placeholder="Search students by name, username, or email..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    handleSearch();
                                }
                            }}
                            className="flex-1"
                        />
                        <Button onClick={handleSearch}>Search</Button>
                        {search && (
                            <Button
                                variant="outline"
                                onClick={() => {
                                    setSearch('');
                                    router.get('/teacher/students');
                                }}
                            >
                                Clear
                            </Button>
                        )}
                    </div>
                </Card>

                {students.data.length === 0 ? (
                    <Card className="flex flex-col items-center justify-center p-12 text-center">
                        <p className="text-muted-foreground">
                            No students found.
                        </p>
                    </Card>
                ) : (
                    <div className="flex flex-col gap-4">
                        <Card>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead className="border-b bg-muted/50">
                                        <tr>
                                            <th className="p-4 text-left text-sm font-medium">
                                                Student
                                            </th>
                                            <th className="p-4 text-left text-sm font-medium">
                                                Trainer Name
                                            </th>
                                            <th className="p-4 text-center text-sm font-medium">
                                                Level
                                            </th>
                                            <th className="p-4 text-center text-sm font-medium">
                                                Quizzes Completed
                                            </th>
                                            <th className="p-4 text-center text-sm font-medium">
                                                Average Score
                                            </th>
                                            <th className="p-4 text-center text-sm font-medium">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {students.data.map((student) => (
                                            <tr
                                                key={student.id}
                                                className="hover:bg-muted/50"
                                            >
                                                <td className="p-4">
                                                    <div className="flex flex-col">
                                                        <span className="font-medium">
                                                            {student.name}
                                                        </span>
                                                        <span className="text-sm text-muted-foreground">
                                                            {student.username}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="p-4">
                                                    {student.trainer_name || (
                                                        <span className="text-sm text-muted-foreground">
                                                            Not set
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="p-4 text-center">
                                                    <Badge variant="outline">
                                                        Lvl {student.level}
                                                    </Badge>
                                                </td>
                                                <td className="p-4 text-center">
                                                    {student.quizzes_completed}
                                                </td>
                                                <td className="p-4 text-center">
                                                    <span
                                                        className={
                                                            student.average_score >=
                                                            80
                                                                ? 'font-medium text-green-600 dark:text-green-400'
                                                                : student.average_score >=
                                                                    60
                                                                  ? 'font-medium text-yellow-600 dark:text-yellow-400'
                                                                  : 'font-medium text-red-600 dark:text-red-400'
                                                        }
                                                    >
                                                        {student.average_score.toFixed(
                                                            1,
                                                        )}
                                                        %
                                                    </span>
                                                </td>
                                                <td className="p-4 text-center">
                                                    <Link
                                                        href={`/teacher/students/${student.id}`}
                                                    >
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                        >
                                                            View Details
                                                        </Button>
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </Card>

                        {students.last_page > 1 && (
                            <div className="flex items-center justify-center gap-2">
                                {Array.from(
                                    { length: students.last_page },
                                    (_, i) => i + 1,
                                ).map((page) => (
                                    <Button
                                        key={page}
                                        variant={
                                            page === students.current_page
                                                ? 'default'
                                                : 'outline'
                                        }
                                        size="sm"
                                        onClick={() =>
                                            router.get(
                                                `/teacher/students?page=${page}`,
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
