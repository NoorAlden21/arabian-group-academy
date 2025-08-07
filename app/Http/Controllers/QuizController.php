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

    public function index(){
        try{
            $user = auth()->user();
            if($user->hasRole('teacher')){
                $quizzes = $this->quizService->getQuizzesForTeacher($user->teacherProfile->id);
            }
            else if($user->hasRole('student')){
                $quizzes = $this->quizService->getQuizzesForStudent($user->studentProfile->classroom_id);
            }//needs to be tested
            else{
                return response()->json([
                    'message' => 'Unauthorized role.'
                ], 403);
            }
    
            return response()->json([
                'quizzes' => QuizBasicInfoResource::collection($quizzes)
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch quizzes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreateQuizRequest $request){
        try{
            $quiz = $this->quizService->createQuiz($request->validated(), auth()->user()->teacherProfile->id);
            return response()->json([
                'message' => 'Quiz created successfully.',
                'quiz' => new QuizFullInfoResource($quiz->load('questions.choices'))
            ],201);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to create quiz.',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function show(Quiz $quiz){
        try{
            return response()->json([
                'quiz' => new QuizFullInfoResource($quiz),
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to fetch quiz.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Quiz $quiz ,UpdateQuizRequest $request){
        try{
            $quiz = $this->quizService->updateQuiz($quiz->id, $request);
            return response()->json([
                'quiz' => $quiz
            ],200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to update quiz',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Quiz $quiz){
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
        }//needs to be tested
    }

    public function assignableClassrooms(Quiz $quiz){
        try{
            $teacher = auth()->user()->teacherProfile;
            $assignedClassrooms = $quiz->classrooms->pluck('id');
            $availableClassrooms = $teacher->classrooms()->whereNotIn('classrooms.id', $assignedClassrooms)->get();    
            
            if ($availableClassrooms->isEmpty()) {
                return response()->json([
                    'message' => 'No classrooms available for assignment.',
                    'classrooms' => []
                ]);
            }

            return response()->json([   
                'classrooms' => ClassroomBasicResource::collection($availableClassrooms)
            ]);
        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function assign(Quiz $quiz, Request $request){
        try{
            $request->validate([
                'classroom_ids' => 'required|array',
                'classroom_ids.*' => 'exists:classrooms,id'
            ]);
    
            $teacher = auth()->user()->teacherProfile;
    
            //another Layer of validation
            $validClassrooms = $teacher->classrooms()
            ->whereIn('classrooms.id', $request->classroom_ids)
            ->pluck('classrooms.id')
            ->toArray();
    
            if(empty($validClassrooms)){
                return response()->json(['message' => 'No valid classrooms to assign.'], 422);
            }

            $quiz->classrooms()->syncWithoutDetaching($validClassrooms);

            return response()->json([
                'message' => 'Quiz assigned successfully to classrooms.',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Failed to assign quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function publishQuiz(){}

    //student part

    public function showForStudent(Quiz $quiz){
        try{
            return response()->json([
                'quiz' => new QuizForStudentResource($quiz),
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to fetch quiz.',
                'error' => $e->getMessage()
            ]);
        }
    }//needs to be tested

    public function studentQuizzes(Request $request){
        try{
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

    public function submitQuiz(Quiz $quiz, Request $request){
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
