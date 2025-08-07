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
use PDO;
use Symfony\Component\Console\Question\ChoiceQuestion;

class QuizService{
    public function getQuizzesForTeacher(int $teacherProfileId){
        return Quiz::with('classrooms')->where('teacher_profile_id', $teacherProfileId)->latest()->get();   
    }

    // public function getQuizzesForStudent(int $classroomId){
    //     return Quiz::with('questions','teacher.user')
    //         ->where('isPublished',true)
    //         ->wherehas('classrooms', function($query) use ($classroomId){
    //             $query->where('classroom_id', $classroomId);
    //         });
    // }

    public function createQuiz(array $data, int $teacherProfileId){
        return DB::transaction(function () use ($data, $teacherProfileId) {
            $quiz = Quiz::create([
                'teacher_profile_id' => $teacherProfileId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'started_at' => $data['started_at'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'is_published' => false,
            ]);

            foreach($data['questions'] as $questionData){
                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $questionData['question_text']
                ]);

                foreach($questionData['choices'] as $choiceData){
                    QuizQuestionChoice::create([
                        'question_id' => $question->id,
                        'choice_text' => $choiceData['choice_text'],
                        'is_correct' => $choiceData['is_correct'],
                    ]);
                }
            }
            return $quiz;
        });
    }

    public function updateQuiz(int $quizId, array $data){
        return DB::transaction(function () use ($quizId, $data){
            $quiz = Quiz::where('id', $quizId)->firstOrFail();

        $quiz->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'started_at' => $data['started_at'],
            'deadline' => $data['deadline'],
        ]);

        foreach($quiz->questions as $question){
            $question->choices()->delete();
            $question->delete();
        }

        foreach($data['questions'] as $questionData){
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_text' => $questionData['question_text'],
            ]);

            foreach($questionData['choices'] as $choiceData){
                QuizQuestionChoice::create([
                    'question_id' => $question->id,
                    'choice_text' => $choiceData['choice_text'],
                    'is_correct' => $choiceData['is_correct'],
                ]);
            }
        }
            return $quiz->fresh('questions.choices');
        });
    }

    public function deleteQuiz(int $quizId){
        return DB::transaction(function() use ($quizId){
            $quiz = Quiz::findOrFail($quizId);
            foreach($quiz->questions as $question){
                $question->choices()->delte();
                $question->delete();
            }

            $quiz->delete();
            
            return true;
        });
    }


    //student part

    public function getStudentQuizzes(StudentProfile $student, ?string $filter = null){
        $submittedQuizIds = $student->quizSubmissions()->pluck('quiz_id')->toArray();

        if($filter === 'all'){
            return $student->quizzes()->get();
        }

        if ($filter === 'submitted') {
            return $student->quizzes()->whereIn('quizzes.id', $submittedQuizIds)->get();
        }
        
        return $student->quizzes()->whereNotIn('quizzes.id', $submittedQuizIds)->get();
    }

    public function submitQuiz(array $data, int $studentProfile){
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