<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnlineQuiz;
use App\Models\OnlineQuizAnswer;
use App\Models\OnlineQuizAttempt;
use App\Models\OnlineQuizQuestion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnlineQuizController extends Controller
{
    public function index(Request $request)
    {
        $query = OnlineQuiz::query()->with(['schoolClass', 'subject'])->withCount('questions')->latest();

        if ($request->filled('school_class_id')) {
            $query->where('school_class_id', $request->integer('school_class_id'));
        }

        return response()->json($query->paginate(20));
    }

    public function show(OnlineQuiz $onlineQuiz)
    {
        $onlineQuiz->load(['schoolClass', 'subject', 'questions']);

        return response()->json(['data' => $onlineQuiz]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_published' => ['sometimes', 'boolean'],
            'questions' => ['sometimes', 'array'],
            'questions.*.question_text' => ['required_with:questions', 'string'],
            'questions.*.question_type' => ['in:mcq,text'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.correct_answer' => ['nullable', 'string', 'max:50'],
            'questions.*.marks' => ['nullable', 'numeric', 'min:0'],
        ]);

        $questions = $data['questions'] ?? [];
        unset($data['questions']);

        $quiz = DB::transaction(function () use ($data, $questions) {
            $quiz = OnlineQuiz::query()->create($data);
            $total = 0;
            foreach ($questions as $i => $q) {
                $marks = (float) ($q['marks'] ?? 1);
                $total += $marks;
                OnlineQuizQuestion::query()->create([
                    'online_quiz_id' => $quiz->id,
                    'question_text' => $q['question_text'],
                    'question_type' => $q['question_type'] ?? 'mcq',
                    'options' => $q['options'] ?? null,
                    'correct_answer' => $q['correct_answer'] ?? null,
                    'marks' => $marks,
                    'sort_order' => $i,
                ]);
            }
            if ($total > 0) {
                $quiz->update(['max_score' => $total]);
            }

            return $quiz->load('questions');
        });

        return response()->json(['data' => $quiz], 201);
    }

    public function update(Request $request, OnlineQuiz $onlineQuiz)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:240'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $onlineQuiz->update($data);

        return response()->json(['data' => $onlineQuiz->fresh(['schoolClass', 'subject', 'questions'])]);
    }

    public function destroy(OnlineQuiz $onlineQuiz)
    {
        $onlineQuiz->delete();

        return response()->json(['message' => 'Quiz deleted.']);
    }

    public function attempts(OnlineQuiz $onlineQuiz)
    {
        return response()->json([
            'data' => $onlineQuiz->attempts()->with('student')->latest()->get(),
        ]);
    }

    public function startAttempt(Request $request, OnlineQuiz $onlineQuiz)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        if (! $onlineQuiz->is_published) {
            return response()->json(['message' => 'Quiz is not published.'], 422);
        }

        $now = now();
        if ($onlineQuiz->starts_at && $now->lt($onlineQuiz->starts_at)) {
            return response()->json(['message' => 'Quiz has not started yet.'], 422);
        }
        if ($onlineQuiz->ends_at && $now->gt($onlineQuiz->ends_at)) {
            return response()->json(['message' => 'Quiz window has ended.'], 422);
        }

        $attempt = OnlineQuizAttempt::query()->firstOrCreate(
            [
                'online_quiz_id' => $onlineQuiz->id,
                'student_id' => $data['student_id'],
            ],
            [
                'started_at' => $now,
                'status' => 'in_progress',
            ]
        );

        if ($attempt->status === 'submitted') {
            return response()->json(['message' => 'Student already submitted this quiz.'], 422);
        }

        $onlineQuiz->load('questions');

        return response()->json([
            'data' => [
                'attempt' => $attempt,
                'quiz' => $onlineQuiz,
                'expires_at' => Carbon::parse($attempt->started_at)->addMinutes($onlineQuiz->duration_minutes),
            ],
        ]);
    }

    public function submitAttempt(Request $request, OnlineQuiz $onlineQuiz, OnlineQuizAttempt $attempt)
    {
        if ($attempt->online_quiz_id !== $onlineQuiz->id) {
            abort(404);
        }
        if ($attempt->status === 'submitted') {
            return response()->json(['message' => 'Already submitted.'], 422);
        }

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:online_quiz_questions,id'],
            'answers.*.answer' => ['nullable', 'string'],
        ]);

        $expires = Carbon::parse($attempt->started_at)->addMinutes($onlineQuiz->duration_minutes);
        $status = now()->gt($expires) ? 'expired' : 'submitted';

        $questions = $onlineQuiz->questions()->get()->keyBy('id');
        $score = 0;

        DB::transaction(function () use ($data, $attempt, $questions, &$score, $status) {
            foreach ($data['answers'] as $row) {
                $question = $questions->get($row['question_id']);
                if (! $question) {
                    continue;
                }
                $answer = (string) ($row['answer'] ?? '');
                $isCorrect = null;
                $marks = 0;
                if ($question->question_type === 'mcq' && $question->correct_answer !== null) {
                    $isCorrect = trim(strtolower($answer)) === trim(strtolower($question->correct_answer));
                    $marks = $isCorrect ? (float) $question->marks : 0;
                    $score += $marks;
                }

                OnlineQuizAnswer::query()->updateOrCreate(
                    [
                        'online_quiz_attempt_id' => $attempt->id,
                        'online_quiz_question_id' => $question->id,
                    ],
                    [
                        'answer' => $answer,
                        'is_correct' => $isCorrect,
                        'marks_awarded' => $question->question_type === 'mcq' ? $marks : null,
                    ]
                );
            }

            $attempt->update([
                'submitted_at' => now(),
                'score' => $score,
                'status' => $status,
            ]);
        });

        return response()->json(['data' => $attempt->fresh(['student', 'answers'])]);
    }
}
