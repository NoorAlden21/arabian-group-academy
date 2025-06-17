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
}
