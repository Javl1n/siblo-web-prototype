<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AiQuizGeneration;
use App\Models\QuizModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Prism\Prism\Facades\Prism;
use Smalot\PdfParser\Parser as PdfParser;

class AiQuizGenerationController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
            'num_questions' => ['required', 'integer', 'min:1', 'max:50'],
            'grade_level' => ['nullable', 'integer', 'min:4', 'max:10'],
            'module_id' => ['nullable', 'integer', 'exists:quiz_modules,id'],
        ]);

        // Get module content if provided
        $moduleContent = null;
        if (isset($validated['module_id'])) {
            $module = QuizModule::find($validated['module_id']);
            $moduleContent = $module->extracted_content;
        }

        // Store generation request
        $generation = AiQuizGeneration::create([
            'teacher_id' => auth()->id(),
            'topic' => $validated['topic'] ?? $validated['subject'],
            'difficulty_level' => $validated['difficulty_level'],
            'number_of_questions' => $validated['num_questions'],
            'generation_prompt' => json_encode($validated),
            'status' => 'pending',
        ]);

        $startTime = now();

        try {
            $generation->update(['status' => 'generating']);

            $questions = $this->generateQuestionsWithAI($validated, $moduleContent);

            $generation->update([
                'ai_response' => json_encode($questions),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return response()->json([
                'generation_id' => $generation->id,
                'questions' => $questions,
            ]);
        } catch (\Exception $e) {
            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return response()->json([
                'error' => 'Failed to generate questions: '.$e->getMessage(),
            ], 500);
        }
    }

    public function uploadModule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'], // 10MB max
        ]);

        $file = $request->file('file');
        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('quiz-modules', $filename, 'local');

        $fileType = $file->getClientOriginalExtension();

        // Extract content from document
        $extractedContent = $this->extractContentFromFile($file->getRealPath(), $fileType);

        $module = QuizModule::create([
            'teacher_id' => auth()->id(),
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_type' => $fileType,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'extracted_content' => $extractedContent,
        ]);

        return response()->json([
            'module_id' => $module->id,
            'filename' => $module->original_filename,
            'file_size' => $module->file_size,
            'content_length' => strlen($extractedContent),
        ]);
    }

    private function extractContentFromFile(string $filePath, string $fileType): string
    {
        try {
            if ($fileType === 'pdf') {
                $parser = new PdfParser;
                $pdf = $parser->parseFile($filePath);

                return $pdf->getText();
            } elseif (in_array($fileType, ['doc', 'docx'])) {
                $phpWord = IOFactory::load($filePath);
                $content = '';

                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $content .= $element->getText().' ';
                        }
                    }
                }

                return trim($content);
            }

            return '';
        } catch (\Exception $e) {
            return 'Error extracting content: '.$e->getMessage();
        }
    }

    private function generateQuestionsWithAI(array $params, ?string $moduleContent): array
    {
        $numQuestions = $params['num_questions'];
        $subject = $params['subject'];
        $topic = $params['topic'] ?? 'General';
        $difficulty = $params['difficulty_level'];
        $gradeLevel = $params['grade_level'] ?? 'grades 4-10';

        // Check if API key is configured
        if (! env('ANTHROPIC_API_KEY') && ! env('OPENAI_API_KEY')) {
            // Return sample questions if no API key
            return $this->generateSampleQuestions($params);
        }

        $basePrompt = "You are an expert educator creating quiz questions in English for {$subject} at difficulty level: {$difficulty} for grade level {$gradeLevel}.";

        if ($moduleContent) {
            $prompt = <<<EOT
{$basePrompt}

Based on the following educational module content, generate {$numQuestions} high-quality quiz questions:

MODULE CONTENT:
{$moduleContent}

Generate {$numQuestions} {$difficulty} difficulty multiple-choice questions that test understanding of the key concepts in this module.

EOT;
        } else {
            $prompt = <<<EOT
{$basePrompt}

Generate {$numQuestions} {$difficulty} difficulty multiple-choice questions about {$topic} in {$subject}.

EOT;
        }

        $prompt .= <<<'EOT'

IMPORTANT: Return ONLY a valid JSON array with this EXACT structure (no markdown, no explanations):
[
  {
    "question_text": "Question here?",
    "question_type": "multiple_choice",
    "points": 1,
    "explanation": "Explanation of the correct answer",
    "choices": [
      {"choice_text": "Option A", "is_correct": true},
      {"choice_text": "Option B", "is_correct": false},
      {"choice_text": "Option C", "is_correct": false},
      {"choice_text": "Option D", "is_correct": false}
    ]
  }
]

Ensure exactly ONE choice is marked as correct for each question. All questions and answers must be in English.
EOT;

        try {
            $provider = env('AI_PROVIDER', 'anthropic');
            $model = env('AI_MODEL', 'claude-3-5-sonnet-latest');

            $response = Prism::text()
                ->using($provider, $model)
                ->withPrompt($prompt)
                ->withMaxTokens(4096)
                ->generate();

            $content = $response->text;

            // Clean up the response
            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/```\s*$/', '', $content);
            $content = trim($content);

            $questions = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from AI: '.json_last_error_msg());
            }

            return $questions;
        } catch (\Exception $e) {
            // Fallback to sample questions
            return $this->generateSampleQuestions($params);
        }
    }

    private function generateSampleQuestions(array $params): array
    {
        $numQuestions = $params['num_questions'];
        $subject = $params['subject'];
        $topic = $params['topic'] ?? 'General';

        $questions = [];

        for ($i = 0; $i < $numQuestions; $i++) {
            $questions[] = [
                'question_text' => "Sample {$subject} question ".($i + 1)." about {$topic}?",
                'question_type' => 'multiple_choice',
                'points' => 1,
                'explanation' => 'This is a sample explanation. Replace with AI-generated content by adding your API key to .env',
                'choices' => [
                    ['choice_text' => 'Option A', 'is_correct' => true],
                    ['choice_text' => 'Option B', 'is_correct' => false],
                    ['choice_text' => 'Option C', 'is_correct' => false],
                    ['choice_text' => 'Option D', 'is_correct' => false],
                ],
            ];
        }

        return $questions;
    }
}
