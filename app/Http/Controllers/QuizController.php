<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Resources\ClassroomBasicResource;
use App\Http\Resources\QuizBasicInfoResource;
use App\Http\Resources\QuizForStudentResource;
use App\Http\Resources\QuizFullInfoResource;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    protected $quizService;
    public function __construct(QuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    public function index()
    {
        try {
            $user = auth()->user();
            if ($user->hasRole('teacher')) {
                $quizzes = $this->quizService->getQuizzesForTeacher($user->teacherProfile->id);
            } else if ($user->hasRole('student')) {
                $quizzes = $this->quizService->getQuizzesForStudent($user->studentProfile->classroom_id);
            } //needs to be tested
            else {
                return response()->json([
                    'message' => 'Unauthorized role.'
                ], 403);
            }

            return response()->json([
                'quizzes' => QuizBasicInfoResource::collection($quizzes->load('subject'))
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch quizzes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreateQuizRequest $request)
    {
        try {
            $data = $request->validated();

            // Attach UploadedFile instances into $data (so the service can store them)
            foreach ($data['questions'] as $qi => &$q) {
                if ($request->hasFile("questions.$qi.question_image")) {
                    $q['question_image'] = $request->file("questions.$qi.question_image");
                }
                foreach ($q['choices'] as $ci => &$c) {
                    if ($request->hasFile("questions.$qi.choices.$ci.choice_image")) {
                        $c['choice_image'] = $request->file("questions.$qi.choices.$ci.choice_image");
                    }
                }
            }

            $quiz = $this->quizService->createQuiz($data, auth()->user()->teacherProfile->id);

            return response()->json([
                'message' => 'Quiz created successfully.',
                'quiz' => new QuizFullInfoResource($quiz->load('questions.choices')),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create quiz.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Quiz $quiz)
    {
        try {
            $quiz->load('subject', 'questions.choices');
            return response()->json([
                'quiz' => new QuizFullInfoResource($quiz),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch quiz.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Quiz $quiz, UpdateQuizRequest $request)
    {
        try {
            $data = $request->validated();

            // Only merge files if questions exist in payload
            if (isset($data['questions'])) {
                foreach ($data['questions'] as $qi => &$q) {
                    if ($request->hasFile("questions.$qi.question_image")) {
                        $q['question_image'] = $request->file("questions.$qi.question_image");
                    }
                    foreach ($q['choices'] as $ci => &$c) {
                        if ($request->hasFile("questions.$qi.choices.$ci.choice_image")) {
                            $c['choice_image'] = $request->file("questions.$qi.choices.$ci.choice_image");
                        }
                    }
                }
            }

            $updated = $this->quizService->updateQuiz($quiz->id, $data);
            $updated->loadMissing('questions.choices', 'subject');

            return response()->json([
                'quiz' => new QuizFullInfoResource($updated)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update quiz',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Quiz $quiz)
    {
        try {
            $this->quizService->deleteQuiz($quiz->id);

            return response()->json([
                'message' => 'Quiz deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function assignableClassrooms(Quiz $quiz)
    {
        try {
            $teacher = auth()->user()->teacherProfile;

            // classrooms this teacher teaches for THIS subject
            $eligibleClassroomIds = $teacher->classSubjectTeachers()
                ->where('subject_id', $quiz->subject_id)
                ->pluck('classroom_id');

            $assigned = $quiz->classrooms()->pluck('classrooms.id');

            $available = Classroom::whereIn('id', $eligibleClassroomIds)
                ->whereNotIn('id', $assigned)
                ->get();

            if ($available->isEmpty()) {
                return response()->json([
                    'message' => 'No classrooms available for assignment.',
                    'classrooms' => []
                ]);
            }

            return response()->json([
                'classrooms' => ClassroomBasicResource::collection($available)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function assign(Quiz $quiz, Request $request)
    {
        try {
            $request->validate([
                'classroom_ids' => 'required|array',
                'classroom_ids.*' => 'exists:classrooms,id'
            ]);

            $teacher = auth()->user()->teacherProfile;

            $validClassroomIds = $teacher->classSubjectTeachers()
                ->where('subject_id', $quiz->subject_id)
                ->whereIn('classroom_id', $request->classroom_ids)
                ->pluck('classroom_id')
                ->toArray();

            if (empty($validClassroomIds)) {
                return response()->json(['message' => 'No valid classrooms to assign.'], 422);
            }

            $quiz->classrooms()->syncWithoutDetaching($validClassroomIds);

            return response()->json([
                'message' => 'Quiz assigned successfully to classrooms.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to assign quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function publish(Quiz $quiz)
    {
        try {
            $quiz = $this->quizService->publishQuiz($quiz->id);
            return response()->json(['quiz' => $quiz]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to publish exam term'], 500);
        }
    }

    //student part

    public function showForStudent(Quiz $quiz)
    {
        try {
            return response()->json([
                'quiz' => new QuizForStudentResource($quiz),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch quiz.',
                'error' => $e->getMessage()
            ]);
        }
    } //needs to be tested

    public function studentQuizzes(Request $request)
    {
        try {
            $student = auth()->user()->studentProfile;
            $filter = $request->query('filter');
            $quizzes = $this->quizService->getStudentQuizzes($student, $filter);
            return response()->json([
                'quizzes' => QuizBasicInfoResource::collection($quizzes)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch quizzes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitQuiz(Quiz $quiz, Request $request)
    {
        try {
            $studentId = auth()->user()->studentProfile->id;

            $submission = $this->quizService->submitQuiz($request->validated(), $studentId);

            return response()->json([
                'message' => 'Quiz submitted successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit quiz.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
