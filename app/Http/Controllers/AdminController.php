<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentBasicInfoResource;
use App\Http\Resources\StudentFullInfoResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\isNull;

class AdminController extends Controller
{
    public function addNewStudent(CreateStudentRequest $request){
        try{
            $validated = $request->validated();
            
            DB::beginTransaction();
            $parent = User::create([
                'name' => $validated['parent_name'],
                'phone_number' => $validated['parent_phone_number'],
                'password' => Hash::make($validated['parent_password']),
            ]);

            $parent->assignRole('parent');
    
            $student = User::create([
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'gender' => $validated['gender'],
                'birth_date' => $validated['birth_date']
            ]);

            $student->assignRole('student');
    
            $student->studentProfile()->create([
                'parent_id' => $parent->id,
                'level' => $validated['level'],
                'enrollment_year' => $validated['enrollment_year'],
                'classroom_id' => $validated['classroom_id'],
            ]);
            DB::commit();
            return response()->json([
                'message' => 'Student and parent created successfully',
                'student_id' => $student->id,
                'parent_id' => $parent->id
            ], 201);
        }catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while creating the student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllStudents(){
        try{
            $students = User::role('student')->get();

            return response()->json([
                'students' => StudentBasicInfoResource::collection($students),
            ],200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to fetch students',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function showStudent($id){
        try{
            $student = User::role('student')
            ->with(['studentProfile.classroom','studentProfile.parent'])
            ->where('id',$id)
            ->first();
            if(is_null($student)){
                return response()->json([
                    'message' => 'Student not found or does not have the student role.'
                ],404);
            }
            return response()->json([
                'student' => new StudentFullInfoResource($student)
            ],200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to fetch student',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function updateStudent($id, UpdateStudentRequest $request){
        try{
            DB::beginTransaction();
            $student = User::role('student')->findOrFail($id);
            $validated = $request->validated();
    
            $student->update([
                'name' => $validated['name'] ?? $student->name,
                 'phone_number' => $validated['phone_number'] ?? $student->phone_number,
                'gender' => $validated['gender'] ?? $student->gender,
                'birth_date' => $validated['birth_date'] ?? $student->birthdate,
            ]);

            if($student->studentProfile){
                $student->studentProfile->update([
                    'level' => $validated['level'] ?? $student->studentProfile->level,
                    'enrollment_year' => $validated['enrollment_year'] ?? $student->studentProfile->enrollment_year,
                    'classroom_id' => $validated['classroom_id'] ?? $student->studentProfile->classroom_id,
                ]);

                if($student->studentProfile->parent){
                    $student->studentProfile->parent->update([
                        'name' => $validated['parent_name'] ?? $student->studentProfile->parent->name,
                        'phone_number' => $validated['parent_phone_number'] ?? $student->studentProfile->parent->phone_number
                    ]);
                }
            }
                DB::commit();

                return response()->json([
                    'message' => 'Student updated successfully',
                    'student' => new StudentFullInfoResource($student)
                ],200);
        }catch(\Exception $e){
                DB::rollBack();
                return response()->json([
                    'message' => 'Update failed',
                    'error' => $e->getMessage()
                ]);
        }
    }

    public function deleteStudent($id){
        try{
            DB::beginTransaction();
            $student = User::role('student')->with('student.studentProfile')->findOrFail($id);
            $parent = $student->studentProfile->parent ?? null;
    
            $student->studentProfile->delete();
            $student->delete();
    
            if($parent && $parent->children()->count() === 0){
                $parent->delete();
            }

            DB::commit();
            return response()->json([
                'message' => 'Student deleted successfully'
            ],200);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete student',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function restoreStudent($id){
        try {
            $student = User::onlyTrashed()->role('student')->where('id', $id)->firstOrFail();
            $student->restore();

            // Optional: restore student profile if it also uses SoftDeletes
            if ($student->studentProfile && method_exists($student->studentProfile, 'restore')) {
                $student->studentProfile->restore();
            }

            return response()->json([
                'message' => 'Student restored successfully',
                'student' => new StudentFullInfoResource($student)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function forceDeleteStudent($id)
    {
        try {
            $student = User::onlyTrashed()->role('student')->where('id', $id)->firstOrFail();

            if ($student->studentProfile) {
                $student->studentProfile()->forceDelete();
            }
            
            $parent = $student->studentProfile->parent ?? null;
            $student->forceDelete();
            if($parent && $parent->children()->count() === 0){
                    $parent->forceDelete();
            }
            
            return response()->json([
                'message' => 'Student permanently deleted'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to permanently delete student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchStudents(Request $request){
        try{
            $query = User::role('student')->with('studentProfile');

            if($request->has('name')){
                $query = $query->where('name','like','%'.$request->name . '%');
            }

            if($request->has('phone_number')){
                $query = $query->where('phone_number','like', '%'. $request->phone_number .'%');
            }

            if($request->has('level')){
                $query->whereHas('studentProfile', function($q) use ($request){
                    $q->where('level', $request->level);
                });
            }

            if($request->has('enrollment_year')){
                $query->whereHas('studentProfile', function($q) use ($request){
                    $q->where('enrollment_year', $request->enrollment_year);
                });
            }

            $students = $query->paginate(10);

            return response()->json([
                'students' => StudentBasicInfoResource::collection($students),
                'pagination' => [
                    'current_page:' => $students->currentPage(),
                    'last_page:' => $students->lastPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                ]
            ],200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to search students',
                'error' => $e->getMessage()
            ],500);
        }
    }
}
