<?php

namespace App\Services;

use App\Http\Requests\CreateStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class StudentService
{
    public function createStudent(array $data)
    {
        return DB::transaction(function () use ($data) {
            $parent = User::create([
                'name' => $data['parent_name'],
                'phone_number' => $data['parent_phone_number'],
                'password' => Hash::make($data['parent_password']),
            ]);
            $parent->parentProfile()->create([
                'occupation' => $data['parent_occupation']
            ]);
            $parent->assignRole('parent');

            $student = User::create([
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                'password' => Hash::make($data['password']),
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
            ]);
            $student->assignRole('student');

            $student->studentProfile()->create([
                'parent_id' => $parent->parentProfile->id,
                'level' => $data['level'],
                'enrollment_year' => $data['enrollment_year'],
                'classroom_id' => $data['classroom_id'],
            ]);

            return $student;
        });
    }

    public function getAllStudents()
    {
        return User::role('student')->get();
    }

    public function getStudentById($id)
    {
        return User::role('student')
            ->with(['studentProfile.classroom', 'studentProfile.parent'])
            ->find($id);
    }

    public function updateStudent($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $student = User::role('student')->findOrFail($id);

            $student->update([
                'name' => $data['name'] ?? $student->name,
                'phone_number' => $data['phone_number'] ?? $student->phone_number,
                'gender' => $data['gender'] ?? $student->gender,
                'birth_date' => $data['birth_date'] ?? $student->birth_date,
            ]);

            if ($student->studentProfile) {
                $student->studentProfile->update([
                    'level' => $data['level'] ?? $student->studentProfile->level,
                    'enrollment_year' => $data['enrollment_year'] ?? $student->studentProfile->enrollment_year,
                    'classroom_id' => $data['classroom_id'] ?? $student->studentProfile->classroom_id,
                ]);

                if ($student->studentProfile->parent) {
                    $student->studentProfile->parent->user->update([
                        'name' => $data['parent_name'] ?? $student->studentProfile->parent->user->name,
                        'phone_number' => $data['parent_phone_number'] ?? $student->studentProfile->parent->user->phone_number,
                    ]);
                }
            }

            return $student;
        });
    }

    public function deleteStudent($id)
    {
        return DB::transaction(function () use ($id) {
            $student = User::role('student')->with('studentProfile')->findOrFail($id);
            $parent = $student->studentProfile->parent ?? null;

            $student->studentProfile->delete();
            $student->delete();

            if ($parent && $parent->children()->count() === 0) {
                $parent->delete();
            }

            return true;
        });
    }

    public function restoreStudent($id)
    {
        $student = User::onlyTrashed()->role('student')->where('id', $id)->firstOrFail();
        $student->restore();

        if ($student->studentProfile && method_exists($student->studentProfile, 'restore')) {
            $student->studentProfile->restore();
        }

        return $student;
    }

    public function forceDeleteStudent($id)
    {

        $student = User::onlyTrashed()->role('student')->where('id', $id)->firstOrFail();

        if ($student->studentProfile) {
            $student->studentProfile()->forceDelete();
        }

        $parent = $student->studentProfile->parent ?? null;
        $student->forceDelete();

        if ($parent && $parent->children()->count() === 0) {
            $parent->forceDelete();
        }

        return true;
    }

    public function searchStudents($filters)
    {

        $query = User::role('student')->with('studentProfile');

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['phone_number'])) {
            $query->where('phone_number', 'like', '%' . $filters['phone_number'] . '%');
        }

        if (!empty($filters['level'])) {
            $query->whereHas('studentProfile', fn($q) => $q->where('level', $filters['level']));
        }

        if (!empty($filters['enrollment_year'])) {
            $query->whereHas('studentProfile', fn($q) => $q->where('enrollment_year', $filters['enrollment_year']));
        }

        return $query->paginate(10);
    }

    public function getStudentsInClassroom(int $classroomId): Collection
    {
        $classroom = Classroom::findOrFail($classroomId);
        $classroom->load('students.user');

        return $classroom->students ?? collect();
    }
}
