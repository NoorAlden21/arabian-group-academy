<?php

namespace App\Services;


use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionChoice;
use App\Models\QuizSubmission;
use App\Models\StudentProfile;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use Symfony\Component\Console\Question\ChoiceQuestion;

class QuizService
{
    public function getQuizzesForTeacher(int $teacherProfileId)
    {
        return Quiz::with('classrooms')->where('teacher_profile_id', $teacherProfileId)->latest()->get();
    }

    // public function getQuizzesForStudent(int $classroomId){
    //     return Quiz::with('questions','teacher.user')
    //         ->where('isPublished',true)
    //         ->wherehas('classrooms', function($query) use ($classroomId){
    //             $query->where('classroom_id', $classroomId);
    //         });
    // }

    public function createQuiz(array $data, int $teacherProfileId)
    {
        return DB::transaction(function () use ($data, $teacherProfileId) {
            $quiz = Quiz::create([
                'teacher_profile_id' => $teacherProfileId,
                'subject_id'         => $data['subject_id'],
                'title'              => $data['title'],
                'description'        => $data['description'] ?? null,
                'started_at'         => $data['started_at'] ?? null,
                'deadline'           => $data['deadline'] ?? null,
                'is_published'       => false,
            ]);


            foreach ($data['questions'] as $qIndex => $questionData) {
                $question = new QuizQuestion();
                $question->quiz_id = $quiz->id;
                $question->question_text = $questionData['question_text'] ?? null;

                // معالجة صورة السؤال إذا كانت موجودة
                if (isset($questionData['question_image']) && $questionData['question_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $path = $questionData['question_image']->store('quiz_questions', 'public');
                    $question->question_image = $path;
                }

                $question->save();

                foreach ($questionData['choices'] as $cIndex => $choiceData) {
                    $choice = new QuizQuestionChoice();
                    $choice->question_id = $question->id;
                    $choice->choice_text = $choiceData['choice_text'] ?? null;
                    $choice->is_correct = $choiceData['is_correct'] ?? false;

                    // معالجة صورة الخيار إذا كانت موجودة
                    if (isset($choiceData['choice_image']) && $choiceData['choice_image'] instanceof \Illuminate\Http\UploadedFile) {
                        $path = $choiceData['choice_image']->store('quiz_choices', 'public');
                        $choice->choice_image = $path;
                    }

                    $choice->save();
                }
            }

            return $quiz->load('questions.choices');
        });
    }


    public function updateQuiz(int $quizId, array $data)
    {
        return DB::transaction(function () use ($quizId, $data) {
            $quiz = Quiz::with('questions.choices')->findOrFail($quizId);

            // If subject_id was sent, associate it explicitly (avoids mass-assignment quirks)
            if (array_key_exists('subject_id', $data)) {
                $quiz->subject()->associate($data['subject_id']);
            }

            // Update other meta fields if provided
            if (array_key_exists('title', $data))       $quiz->title       = $data['title'];
            if (array_key_exists('description', $data)) $quiz->description = $data['description'];
            if (array_key_exists('started_at', $data))  $quiz->started_at  = $data['started_at'];
            if (array_key_exists('deadline', $data))    $quiz->deadline    = $data['deadline'];

            $quiz->save();

            // If no questions provided, keep existing Q&A
            if (!array_key_exists('questions', $data)) {
                return $quiz->refresh()->load('questions.choices', 'subject');
            }

            // Remove old images then delete rows
            foreach ($quiz->questions as $oldQ) {
                if (!empty($oldQ->question_image)) {
                    Storage::disk('public')->delete($oldQ->question_image);
                }
                foreach ($oldQ->choices as $oldC) {
                    if (!empty($oldC->choice_image)) {
                        Storage::disk('public')->delete($oldC->choice_image);
                    }
                }
            }
            foreach ($quiz->questions as $oldQ) {
                $oldQ->choices()->delete();
                $oldQ->delete();
            }

            // Recreate Q&A
            foreach ($data['questions'] as $qData) {
                $question = new QuizQuestion([
                    'quiz_id'       => $quiz->id,
                    'question_text' => $qData['question_text'] ?? null,
                ]);
                if (isset($qData['question_image']) && $qData['question_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $question->question_image = $qData['question_image']->store('quiz_questions', 'public');
                }
                $question->save();

                foreach ($qData['choices'] as $cData) {
                    $choice = new QuizQuestionChoice([
                        'question_id' => $question->id,
                        'choice_text' => $cData['choice_text'] ?? null,
                        'is_correct'  => in_array(($cData['is_correct'] ?? null), [true, 'true', 1, '1', 'on'], true),
                    ]);
                    if (isset($cData['choice_image']) && $cData['choice_image'] instanceof \Illuminate\Http\UploadedFile) {
                        $choice->choice_image = $cData['choice_image']->store('quiz_choices', 'public');
                    }
                    $choice->save();
                }
            }

            return Quiz::with('questions.choices', 'subject')->findOrFail($quiz->id);
        });
    }

    public function deleteQuiz(int $quizId)
    {
        return DB::transaction(function () use ($quizId) {
            $quiz = Quiz::findOrFail($quizId);
            foreach ($quiz->questions as $question) {
                $question->choices()->delete();
                $question->delete();
            }

            $quiz->delete();

            return true;
        });
    }

    public function publishQuiz(int $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $quiz->update(['is_published' => true]);
        return $quiz->fresh();
    }


    //student part

    public function getStudentQuizzes(StudentProfile $student, ?string $filter = null)
    {
        $now = now();

        $submittedQuizIds = $student->quizSubmissions()->pluck('quiz_id')->toArray();

        $base = $student->quizzes()->with('subject');

        switch ($filter) {
            case 'submitted':
                return (clone $base)
                    ->whereIn('quizzes.id', $submittedQuizIds)
                    ->get();

            case 'all':
                return (clone $base)
                    ->where('is_published', true)
                    ->get();

            case 'unsubmitted':
            default:
                return (clone $base)
                    ->where('is_published', true)
                    ->whereNotIn('quizzes.id', $submittedQuizIds)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('started_at')
                            ->orWhere('started_at', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('deadline')
                            ->orWhere('deadline', '>=', $now);
                    })
                    ->get();
        }
    }

    public function submitQuiz(array $data, int $studentProfile)
    {
        return DB::transaction(function () use ($data, $studentProfile) {
            $quiz = Quiz::with('questions.choices')->findOrFail($data['quiz_id']);

            $correctCount = 0;
            $totalQuestions = $quiz->questions->count();

            $submission = QuizSubmission::create([
                'quiz_id' => $quiz->id,
                'student_profile_id' => $studentProfile,
                'submitted_at' => now(),
            ]);

            foreach ($data['answers'] as $answer) {
                $question = $quiz->questions->firstWhere('id', $answer['question_id']);

                $selectedChoice = collect($question->choices)->firstWhere('id', $answer['selected_choice_id']);
                $isCorrect = $selectedChoice && $selectedChoice->is_correct;

                if ($isCorrect) {
                    $correctCount++;
                }

                QuizAnswer::create([
                    'submission_id' => $submission->id,
                    'question_id' => $answer['question_id'],
                    'selected_choice_id' => $answer['selected_choice_id'],
                ]);
            }

            $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

            $submission->update(['score' => $score]);

            return $submission;
        });
    }
}
