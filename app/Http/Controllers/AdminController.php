<?php

namespace App\Http\Controllers;

use App\Http\Requests\createStudentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function addNewStudent(createStudentRequest $request){
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
                'birthdate' => $validated['birthdate']
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
}
