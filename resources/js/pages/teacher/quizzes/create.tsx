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

interface Question {
    question_text: string;
    question_type: string;
    points: number;
    explanation: string;
    choices: Choice[];
}

interface Choice {
    choice_text: string;
    is_correct: boolean;
}

interface QuizModule {
    id: number;
    filename: string;
    original_filename: string;
    file_size: number;
    content_length: number;
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
    {
        title: 'Create Quiz',
        href: '/teacher/quizzes/create',
    },
];

const subjects = [
    'Math',
    'Science',
    'English',
    'Filipino',
    'Araling Panlipunan',
    'History',
    'Geography',
];

export default function Create() {
    const [showAiModal, setShowAiModal] = useState(false);
    const [questions, setQuestions] = useState<Question[]>([
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

    // AI Generation state
    const [aiSubject, setAiSubject] = useState('');
    const [aiTopic, setAiTopic] = useState('');
    const [aiDifficulty, setAiDifficulty] = useState('medium');
    const [aiNumQuestions, setAiNumQuestions] = useState(5);
    const [aiGradeLevel, setAiGradeLevel] = useState(7);
    const [selectedModule, setSelectedModule] = useState<number | null>(null);
    const [isGenerating, setIsGenerating] = useState(false);
    const [generatedQuestions, setGeneratedQuestions] = useState<
        Question[] | null
    >(null);

    // Module upload state
    const [uploadedModules, setUploadedModules] = useState<QuizModule[]>([]);
    const [isUploading, setIsUploading] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);

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

    const handleFileUpload = async (
        event: React.ChangeEvent<HTMLInputElement>,
    ) => {
        const file = event.target.files?.[0];
        if (!file) return;

        setIsUploading(true);
        setUploadError(null);

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch('/teacher/ai/upload-module', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
            });

            if (!response.ok) {
                throw new Error('Upload failed');
            }

            const data = await response.json();
            setUploadedModules([...uploadedModules, data]);
            event.target.value = '';
        } catch (error) {
            setUploadError('Failed to upload module. Please try again.');
        } finally {
            setIsUploading(false);
        }
    };

    const handleGenerateQuestions = async () => {
        if (!aiSubject) {
            alert('Please select a subject');
            return;
        }

        setIsGenerating(true);

        try {
            const response = await fetch('/teacher/ai/generate-quiz', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    subject: aiSubject,
                    topic: aiTopic || null,
                    difficulty_level: aiDifficulty,
                    num_questions: aiNumQuestions,
                    grade_level: aiGradeLevel,
                    module_id: selectedModule,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                const errorMsg = data.error || 'Generation failed';
                alert(errorMsg);
                return;
            }

            setGeneratedQuestions(data.questions);
        } catch (error) {
            alert(
                'Failed to generate questions. Please check your AI configuration in .env file.',
            );
        } finally {
            setIsGenerating(false);
        }
    };

    const applyGeneratedQuestions = () => {
        if (generatedQuestions) {
            setQuestions(generatedQuestions);
            setShowAiModal(false);
            setGeneratedQuestions(null);
        }
    };

    const resetAiModal = () => {
        setShowAiModal(false);
        setGeneratedQuestions(null);
        setIsGenerating(false);
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
            alert(
                'Please fix the following errors:\n\n' + errors.join('\n'),
            );
            return false;
        }

        return true;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Quiz" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Create Quiz
                        </h1>
                        <p className="text-muted-foreground">
                            Create a new quiz manually or use AI to generate
                            questions
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        onClick={() => setShowAiModal(true)}
                    >
                        Generate with AI
                    </Button>
                </div>

                <Form
                    action="/teacher/quizzes"
                    method="post"
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
                                        <Select name="subject" required>
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
                                            defaultValue="medium"
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
                                            defaultValue="60"
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
                                                    <Label>Question Text *</Label>
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
                                                    {errors[`questions.${qIndex}.question_text`] && (
                                                        <p className="text-sm text-red-600">
                                                            {errors[`questions.${qIndex}.question_text`]}
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
                                                                addChoice(qIndex)
                                                            }
                                                        >
                                                            Add Choice
                                                        </Button>
                                                    </div>
                                                    <div className="space-y-2">
                                                        {question.choices.map(
                                                            (choice, cIndex) => (
                                                                <div
                                                                    key={cIndex}
                                                                    className="flex items-center gap-2"
                                                                >
                                                                    <input
                                                                        type="hidden"
                                                                        name={`questions[${qIndex}][choices][${cIndex}][is_correct]`}
                                                                        value={choice.is_correct ? '1' : '0'}
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
                                                        Check the box to mark the
                                                        correct answer
                                                    </p>
                                                    {errors[`questions.${qIndex}.choices`] && (
                                                        <p className="text-sm text-red-600">
                                                            {errors[`questions.${qIndex}.choices`]}
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
                                    Create Quiz
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() =>
                                        (window.location.href =
                                            '/teacher/quizzes')
                                    }
                                >
                                    Cancel
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                {showAiModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4">
                        <Card className="my-8 w-full max-w-4xl p-6">
                            <h2 className="mb-4 text-2xl font-bold">
                                Generate Quiz with AI
                            </h2>

                            {!generatedQuestions ? (
                                <div className="space-y-6">
                                    {/* Module Upload Section */}
                                    <div className="rounded-lg border p-4">
                                        <h3 className="mb-3 font-semibold">
                                            Upload Module (Optional)
                                        </h3>
                                        <p className="mb-3 text-sm text-muted-foreground">
                                            Upload a PDF or Word document to
                                            generate questions based on the
                                            module content.
                                        </p>

                                        <div className="mb-3">
                                            <Input
                                                type="file"
                                                accept=".pdf,.doc,.docx"
                                                onChange={handleFileUpload}
                                                disabled={isUploading}
                                            />
                                            {isUploading && (
                                                <p className="mt-2 text-sm text-muted-foreground">
                                                    Uploading...
                                                </p>
                                            )}
                                            {uploadError && (
                                                <p className="mt-2 text-sm text-red-600">
                                                    {uploadError}
                                                </p>
                                            )}
                                        </div>

                                        {uploadedModules.length > 0 && (
                                            <div className="space-y-2">
                                                <Label>Uploaded Modules</Label>
                                                <div className="space-y-2">
                                                    {uploadedModules.map(
                                                        (module) => (
                                                            <div
                                                                key={module.id}
                                                                className="flex items-center justify-between rounded border p-2"
                                                            >
                                                                <div className="flex-1">
                                                                    <p className="text-sm font-medium">
                                                                        {
                                                                            module.original_filename
                                                                        }
                                                                    </p>
                                                                    <p className="text-xs text-muted-foreground">
                                                                        {(
                                                                            module.file_size /
                                                                            1024
                                                                        ).toFixed(
                                                                            1,
                                                                        )}{' '}
                                                                        KB •{' '}
                                                                        {
                                                                            module.content_length
                                                                        }{' '}
                                                                        chars
                                                                        extracted
                                                                    </p>
                                                                </div>
                                                                <Button
                                                                    type="button"
                                                                    variant={
                                                                        selectedModule ===
                                                                        module.id
                                                                            ? 'default'
                                                                            : 'outline'
                                                                    }
                                                                    size="sm"
                                                                    onClick={() =>
                                                                        setSelectedModule(
                                                                            selectedModule ===
                                                                                module.id
                                                                                ? null
                                                                                : module.id,
                                                                        )
                                                                    }
                                                                >
                                                                    {selectedModule ===
                                                                    module.id
                                                                        ? 'Selected'
                                                                        : 'Select'}
                                                                </Button>
                                                            </div>
                                                        ),
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    {/* Generation Parameters */}
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label>Subject *</Label>
                                            <Select
                                                value={aiSubject}
                                                onValueChange={setAiSubject}
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
                                        </div>

                                        <div className="space-y-2">
                                            <Label>Topic</Label>
                                            <Input
                                                value={aiTopic}
                                                onChange={(e) =>
                                                    setAiTopic(e.target.value)
                                                }
                                                placeholder="e.g., Photosynthesis"
                                            />
                                        </div>

                                        <div className="space-y-2">
                                            <Label>Difficulty</Label>
                                            <Select
                                                value={aiDifficulty}
                                                onValueChange={setAiDifficulty}
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
                                            <Label>Grade Level (4-10)</Label>
                                            <Input
                                                type="number"
                                                min="4"
                                                max="10"
                                                value={aiGradeLevel}
                                                onChange={(e) =>
                                                    setAiGradeLevel(
                                                        parseInt(
                                                            e.target.value,
                                                        ) || 7,
                                                    )
                                                }
                                            />
                                        </div>

                                        <div className="space-y-2 md:col-span-2">
                                            <Label>
                                                Number of Questions (1-50)
                                            </Label>
                                            <div className="flex items-center gap-4">
                                                <Input
                                                    type="range"
                                                    min="1"
                                                    max="50"
                                                    value={aiNumQuestions}
                                                    onChange={(e) =>
                                                        setAiNumQuestions(
                                                            parseInt(
                                                                e.target.value,
                                                            ),
                                                        )
                                                    }
                                                    className="flex-1"
                                                />
                                                <span className="w-12 text-center font-medium">
                                                    {aiNumQuestions}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex gap-2">
                                        <Button
                                            onClick={handleGenerateQuestions}
                                            disabled={isGenerating}
                                        >
                                            {isGenerating && <Spinner />}
                                            {isGenerating
                                                ? 'Generating...'
                                                : 'Generate Questions'}
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={resetAiModal}
                                        >
                                            Cancel
                                        </Button>
                                    </div>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {/* Preview Generated Questions */}
                                    <div className="rounded-lg border p-4">
                                        <h3 className="mb-3 font-semibold">
                                            Generated Questions Preview
                                        </h3>
                                        <p className="mb-4 text-sm text-muted-foreground">
                                            Review the {generatedQuestions.length}{' '}
                                            generated questions below. Click
                                            "Apply Questions" to add them to your
                                            quiz.
                                        </p>

                                        <div className="max-h-96 space-y-4 overflow-y-auto">
                                            {generatedQuestions.map(
                                                (question, index) => (
                                                    <Card
                                                        key={index}
                                                        className="p-4"
                                                    >
                                                        <div className="mb-2 font-medium">
                                                            {index + 1}.{' '}
                                                            {
                                                                question.question_text
                                                            }
                                                        </div>
                                                        <div className="space-y-1 pl-4">
                                                            {question.choices.map(
                                                                (
                                                                    choice,
                                                                    cIndex,
                                                                ) => (
                                                                    <div
                                                                        key={
                                                                            cIndex
                                                                        }
                                                                        className={
                                                                            choice.is_correct
                                                                                ? 'font-medium text-green-600 dark:text-green-400'
                                                                                : 'text-muted-foreground'
                                                                        }
                                                                    >
                                                                        {choice.is_correct
                                                                            ? '✓'
                                                                            : '○'}{' '}
                                                                        {
                                                                            choice.choice_text
                                                                        }
                                                                    </div>
                                                                ),
                                                            )}
                                                        </div>
                                                        {question.explanation && (
                                                            <div className="mt-2 border-t pt-2 text-sm text-muted-foreground">
                                                                <span className="font-medium">
                                                                    Explanation:
                                                                </span>{' '}
                                                                {
                                                                    question.explanation
                                                                }
                                                            </div>
                                                        )}
                                                    </Card>
                                                ),
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex gap-2">
                                        <Button onClick={applyGeneratedQuestions}>
                                            Apply Questions
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={() =>
                                                setGeneratedQuestions(null)
                                            }
                                        >
                                            Generate Again
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={resetAiModal}
                                        >
                                            Cancel
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </Card>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
