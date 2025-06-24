<?php

namespace App\Services;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;


class ClassroomService
{


    public function createClassroom(array $data): Classroom
    {
        return DB::transaction(function () use ($data) {
            $classroom = Classroom::create($data);
            return $classroom;
        });
    }


    public function getAllClassrooms()
    {
        return Classroom::all();
    }


    public function getClassroomById(int $id, array $relations = ['students', 'classSubjectTeachers'])
    {
        return Classroom::with($relations)->find($id);
    }


    public function updateClassroom(int $id, array $data): Classroom
    {
        return DB::transaction(function () use ($id, $data) {
            $classroom = Classroom::findOrFail($id);
            $classroom->update($data);
            return $classroom;
        });
    }

    public function deleteClassroom(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $classroom = Classroom::findOrFail($id);
            return $classroom->delete();
        });
    }


    public function restoreClassroom(int $id): Classroom
    {
        $classroom = Classroom::onlyTrashed()->findOrFail($id);
        $classroom->restore();
        return $classroom;
    }


    public function forceDeleteClassroom(int $id): bool
    {

        $classroom = Classroom::withTrashed()->findOrFail($id);

        return $classroom->forceDelete();
    }

    public function searchClassrooms(array $filters, int $perPage = 10)
    {
        $query = Classroom::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }


        return $query->paginate($perPage);
    }

    public function getOnlyTrashedClassrooms()
    {
        return Classroom::onlyTrashed()->get();
    }

   public function getEligibleTeachers(int $id){

    $classroom = Classroom::with('classType.classTypeSubjects.subject')->findOrFail($id);
    $classType = $classroom->classType;

    $subjects = [];

    foreach ($classType->classTypeSubjects as $cts) {
        $teachers = $cts->teachers()->with('user')->get();

        $subjects[] = [
            'subject_id' => $cts->subject->id,
            'subject_name' => $cts->subject->name,
            'teachers' => $teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name
                ];
                })->toArray()
            ];
        }

        return [
            'classroom_name' => $classroom->name,
            'classroom_type' => $classType->name,
            'subjects' => $subjects
        ];
    }
}
