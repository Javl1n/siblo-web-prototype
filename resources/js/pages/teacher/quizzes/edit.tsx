import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, router } from '@inertiajs/react';
import { useState } from 'react';

interface Choice {
    id?: number;
    choice_text: string;
    is_correct: boolean;
}

interface Question {
    id?: number;
    question_text: string;
    question_type: string;
    points: number;
    explanation: string;
    choices: Choice[];
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
    is_published: boolean;
    is_featured: boolean;
    questions: Question[];
}

interface Props {
    quiz: Quiz;
}

const subjects = [
    'Math',
    'Science',
    'English',
    'Filipino',
    'Araling Panlipunan',
    'History',
    'Geography',
];

export default function Edit({ quiz }: Props) {
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
        {
            title: 'Edit',
            href: `/teacher/quizzes/${quiz.id}/edit`,
        },
    ];

    const [questions, setQuestions] = useState<Question[]>(quiz.questions);

    const addQuestion = () => {
        setQuestions([
            ...questions,
            {
                question_text: '',
                question_type: 'multiple_choice',
                points: 1,
                explanation: '',
                choices: [
                    { choice_text: '', is_correct: false },
                    { choice_text: '', is_correct: false },
                    { choice_text: '', is_correct: false },
                    { choice_text: '', is_correct: false },
                ],
            },
        ]);
    };

    const removeQuestion = (index: number) => {
        if (questions.length > 1) {
            setQuestions(questions.filter((_, i) => i !== index));
        }
    };

    const updateQuestion = (index: number, field: string, value: any) => {
        const updated = [...questions];
        updated[index] = { ...updated[index], [field]: value };
        setQuestions(updated);
    };

    const updateChoice = (
        qIndex: number,
        cIndex: number,
        field: string,
        value: any,
    ) => {
        const updated = [...questions];
        updated[qIndex].choices[cIndex] = {
            ...updated[qIndex].choices[cIndex],
            [field]: value,
        };
        setQuestions(updated);
    };

    const addChoice = (qIndex: number) => {
        const updated = [...questions];
        updated[qIndex].choices.push({ choice_text: '', is_correct: false });
        setQuestions(updated);
    };

    const removeChoice = (qIndex: number, cIndex: number) => {
        const updated = [...questions];
        if (updated[qIndex].choices.length > 2) {
            updated[qIndex].choices = updated[qIndex].choices.filter(
                (_, i) => i !== cIndex,
            );
            setQuestions(updated);
        }
    };

    const validateQuestions = () => {
        const errors: string[] = [];

        questions.forEach((question, qIndex) => {
            const correctChoices = question.choices.filter(
                (c) => c.is_correct,
            ).length;

            if (correctChoices === 0) {
                errors.push(
                    `Question ${qIndex + 1}: At least one choice must be marked as correct`,
                );
            }

            if (question.question_text.trim() === '') {
                errors.push(
                    `Question ${qIndex + 1}: Question text is required`,
                );
            }

            question.choices.forEach((choice, cIndex) => {
                if (choice.choice_text.trim() === '') {
                    errors.push(
                        `Question ${qIndex + 1}, Choice ${cIndex + 1}: Choice text is required`,
                    );
                }
            });
        });

        if (errors.length > 0) {
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
            return false;
        }

        return true;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${quiz.title}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Edit Quiz
                        </h1>
                        <p className="text-muted-foreground">
                            Update quiz information and questions
                        </p>
                    </div>
                </div>

                <Form
                    action={`/teacher/quizzes/${quiz.id}`}
                    method="put"
                    transform={(data) => ({
                        ...data,
                        is_published: data.is_published ? true : false,
                        is_featured: data.is_featured ? true : false,
                    })}
                    onSubmit={(e) => {
                        if (!validateQuestions()) {
                            e.preventDefault();
                        }
                    }}
                >
                    {({ processing, errors }) => (
                        <>
                            <Card className="p-6">
                                <h2 className="mb-4 text-xl font-semibold">
                                    Quiz Information
                                </h2>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="title">
                                            Quiz Title *
                                        </Label>
                                        <Input
                                            id="title"
                                            name="title"
                                            required
                                            defaultValue={quiz.title}
                                            placeholder="Grade 5 Math - Fractions"
                                        />
                                        {errors.title && (
                                            <p className="text-sm text-red-600">
                                                {errors.title}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="subject">
                                            Subject *
                                        </Label>
                                        <Select
                                            name="subject"
                                            required
                                            defaultValue={quiz.subject}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select subject" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {subjects.map((subject) => (
                                                    <SelectItem
                                                        key={subject}
                                                        value={subject}
                                                    >
                                                        {subject}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.subject && (
                                            <p className="text-sm text-red-600">
                                                {errors.subject}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="topic">Topic</Label>
                                        <Input
                                            id="topic"
                                            name="topic"
                                            defaultValue={quiz.topic || ''}
                                            placeholder="e.g., Adding Fractions"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="difficulty_level">
                                            Difficulty *
                                        </Label>
                                        <Select
                                            name="difficulty_level"
                                            required
                                            defaultValue={quiz.difficulty_level}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="easy">
                                                    Easy
                                                </SelectItem>
                                                <SelectItem value="medium">
                                                    Medium
                                                </SelectItem>
                                                <SelectItem value="hard">
                                                    Hard
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="time_limit_minutes">
                                            Time Limit (minutes)
                                        </Label>
                                        <Input
                                            id="time_limit_minutes"
                                            name="time_limit_minutes"
                                            type="number"
                                            min="1"
                                            defaultValue={
                                                quiz.time_limit_minutes || ''
                                            }
                                            placeholder="Optional"
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="pass_threshold">
                                            Pass Threshold (%) *
                                        </Label>
                                        <Input
                                            id="pass_threshold"
                                            name="pass_threshold"
                                            type="number"
                                            min="0"
                                            max="100"
                                            defaultValue={quiz.pass_threshold}
                                            required
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="max_attempts">
                                            Max Attempts
                                        </Label>
                                        <Input
                                            id="max_attempts"
                                            name="max_attempts"
                                            type="number"
                                            min="1"
                                            defaultValue={
                                                quiz.max_attempts || ''
                                            }
                                            placeholder="Unlimited if empty"
                                        />
                                    </div>

                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="description">
                                            Description
                                        </Label>
                                        <textarea
                                            id="description"
                                            name="description"
                                            defaultValue={
                                                quiz.description || ''
                                            }
                                            className="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                            placeholder="Brief description of the quiz..."
                                        />
                                    </div>

                                    <div className="flex items-center gap-4 md:col-span-2">
                                        <label className="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                name="is_published"
                                                value="1"
                                                defaultChecked={
                                                    quiz.is_published
                                                }
                                                className="rounded"
                                            />
                                            <span className="text-sm">
                                                Publish immediately
                                            </span>
                                        </label>
                                        <label className="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                name="is_featured"
                                                value="1"
                                                defaultChecked={
                                                    quiz.is_featured
                                                }
                                                className="rounded"
                                            />
                                            <span className="text-sm">
                                                Mark as featured
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </Card>

                            <Card className="p-6">
                                <div className="mb-4 flex items-center justify-between">
                                    <h2 className="text-xl font-semibold">
                                        Questions
                                    </h2>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={addQuestion}
                                    >
                                        Add Question
                                    </Button>
                                </div>

                                <div className="space-y-6">
                                    {questions.map((question, qIndex) => (
                                        <Card key={qIndex} className="p-4">
                                            <div className="mb-4 flex items-center justify-between">
                                                <h3 className="font-semibold">
                                                    Question {qIndex + 1}
                                                </h3>
                                                {questions.length > 1 && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            removeQuestion(
                                                                qIndex,
                                                            )
                                                        }
                                                    >
                                                        Remove
                                                    </Button>
                                                )}
                                            </div>

                                            <div className="space-y-4">
                                                <div>
                                                    <Label>
                                                        Question Text *
                                                    </Label>
                                                    <textarea
                                                        name={`questions[${qIndex}][question_text]`}
                                                        value={
                                                            question.question_text
                                                        }
                                                        onChange={(e) =>
                                                            updateQuestion(
                                                                qIndex,
                                                                'question_text',
                                                                e.target.value,
                                                            )
                                                        }
                                                        required
                                                        className="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                        placeholder="Enter your question..."
                                                    />
                                                    {errors[
                                                        `questions.${qIndex}.question_text`
                                                    ] && (
                                                        <p className="text-sm text-red-600">
                                                            {
                                                                errors[
                                                                    `questions.${qIndex}.question_text`
                                                                ]
                                                            }
                                                        </p>
                                                    )}
                                                </div>

                                                <div className="grid gap-4 md:grid-cols-2">
                                                    <div>
                                                        <Label>
                                                            Question Type
                                                        </Label>
                                                        <Select
                                                            name={`questions[${qIndex}][question_type]`}
                                                            value={
                                                                question.question_type
                                                            }
                                                            onValueChange={(
                                                                value,
                                                            ) =>
                                                                updateQuestion(
                                                                    qIndex,
                                                                    'question_type',
                                                                    value,
                                                                )
                                                            }
                                                        >
                                                            <SelectTrigger>
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="multiple_choice">
                                                                    Multiple
                                                                    Choice
                                                                </SelectItem>
                                                                <SelectItem value="true_false">
                                                                    True/False
                                                                </SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                    </div>

                                                    <div>
                                                        <Label>Points</Label>
                                                        <Input
                                                            type="number"
                                                            name={`questions[${qIndex}][points]`}
                                                            value={
                                                                question.points
                                                            }
                                                            onChange={(e) =>
                                                                updateQuestion(
                                                                    qIndex,
                                                                    'points',
                                                                    parseInt(
                                                                        e.target
                                                                            .value,
                                                                    ),
                                                                )
                                                            }
                                                            min="1"
                                                        />
                                                    </div>
                                                </div>

                                                <div>
                                                    <div className="mb-2 flex items-center justify-between">
                                                        <Label>
                                                            Answer Choices *
                                                        </Label>
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() =>
                                                                addChoice(
                                                                    qIndex,
                                                                )
                                                            }
                                                        >
                                                            Add Choice
                                                        </Button>
                                                    </div>
                                                    <div className="space-y-2">
                                                        {question.choices.map(
                                                            (
                                                                choice,
                                                                cIndex,
                                                            ) => (
                                                                <div
                                                                    key={cIndex}
                                                                    className="flex items-center gap-2"
                                                                >
                                                                    <input
                                                                        type="hidden"
                                                                        name={`questions[${qIndex}][choices][${cIndex}][is_correct]`}
                                                                        value={
                                                                            choice.is_correct
                                                                                ? '1'
                                                                                : '0'
                                                                        }
                                                                    />
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={
                                                                            choice.is_correct
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            updateChoice(
                                                                                qIndex,
                                                                                cIndex,
                                                                                'is_correct',
                                                                                e
                                                                                    .target
                                                                                    .checked,
                                                                            )
                                                                        }
                                                                        className="rounded"
                                                                    />
                                                                    <Input
                                                                        name={`questions[${qIndex}][choices][${cIndex}][choice_text]`}
                                                                        value={
                                                                            choice.choice_text
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            updateChoice(
                                                                                qIndex,
                                                                                cIndex,
                                                                                'choice_text',
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                        placeholder={`Choice ${cIndex + 1}`}
                                                                        className="flex-1"
                                                                        required
                                                                    />
                                                                    {question
                                                                        .choices
                                                                        .length >
                                                                        2 && (
                                                                        <Button
                                                                            type="button"
                                                                            variant="ghost"
                                                                            size="sm"
                                                                            onClick={() =>
                                                                                removeChoice(
                                                                                    qIndex,
                                                                                    cIndex,
                                                                                )
                                                                            }
                                                                        >
                                                                            �
                                                                        </Button>
                                                                    )}
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                        Check the box to mark
                                                        the correct answer
                                                    </p>
                                                    {errors[
                                                        `questions.${qIndex}.choices`
                                                    ] && (
                                                        <p className="text-sm text-red-600">
                                                            {
                                                                errors[
                                                                    `questions.${qIndex}.choices`
                                                                ]
                                                            }
                                                        </p>
                                                    )}
                                                </div>

                                                <div>
                                                    <Label>Explanation</Label>
                                                    <textarea
                                                        name={`questions[${qIndex}][explanation]`}
                                                        value={
                                                            question.explanation
                                                        }
                                                        onChange={(e) =>
                                                            updateQuestion(
                                                                qIndex,
                                                                'explanation',
                                                                e.target.value,
                                                            )
                                                        }
                                                        className="min-h-16 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                        placeholder="Optional: Explain the correct answer..."
                                                    />
                                                </div>
                                            </div>
                                        </Card>
                                    ))}
                                </div>
                            </Card>

                            {errors.questions && (
                                <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600 dark:border-red-800 dark:bg-red-950">
                                    {errors.questions}
                                </div>
                            )}

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner />}
                                    Update Quiz
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() =>
                                        router.visit(
                                            `/teacher/quizzes/${quiz.id}`,
                                        )
                                    }
                                >
                                    Cancel
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
