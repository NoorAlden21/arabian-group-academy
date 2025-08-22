<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class SubjectService
{
    public function getAllSubjects() {
        return Subject::with('classTypes')->get();
    }

    public function getSubjectById(int $id): Subject {
        $subject = Subject::with('classTypes')->find($id);
        if (!$subject) {
            throw new ModelNotFoundException("Subject {$id} not found");
        }
        return $subject;
    }

    public function createSubject(array $data): Subject {
    return DB::transaction(function () use ($data) {
        $subject = Subject::create(['name' => $data['name']]);

        if (array_key_exists('class_type_ids', $data) && is_array($data['class_type_ids'])) {
            $subject->classTypes()->sync($data['class_type_ids']);
        }

        return $subject->load('classTypes');
        });
    }

    public function updateSubject(int $id, array $data): Subject{
        return DB::transaction(function () use ($id, $data) {
            $subject = Subject::findOrFail($id);

            if (array_key_exists('name', $data)) {
                $subject->update(['name' => $data['name']]);
            }

            //null nothing happens [] cancle the current relations
            if (array_key_exists('class_type_ids', $data) && is_array($data['class_type_ids'])) {
                $subject->classTypes()->sync($data['class_type_ids']);
            }

            return $subject->load('classTypes');
        });
    }

    public function deleteSubject(int $id): void
    {
        $subject = Subject::find($id);
        if (!$subject) {
            throw new ModelNotFoundException("Subject {$id} not found");
        }

        DB::transaction(function () use ($subject) {
            $subject->delete();
        });
    }
}
